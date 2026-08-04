<?php

namespace App\Http\Controllers;

use App\Models\Magang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MagangController extends Controller
{
    /**
     * INDEX
     */
    private function getCvUrl($cvPath)
    {
        if (!$cvPath) return null;
        if (str_starts_with($cvPath, 'http://') || str_starts_with($cvPath, 'https://')) {
            return $cvPath;
        }
        $cleanPath = ltrim($cvPath, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }
        return url('api/storage/' . $cleanPath);
    }

    /**
     * SERVE FILE VIA API ROUTE (Bypasses webserver static file 404 blocks)
     */
    public function serveFile($path)
    {
        $cleanPath = ltrim(urldecode($path), '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        $fullPath = storage_path('app/public/' . $cleanPath);

        if (!file_exists($fullPath)) {
            $altPath = storage_path('app/' . $cleanPath);
            if (file_exists($altPath)) {
                $fullPath = $altPath;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan di server disk.'
                ], 404);
            }
        }

        return response()->file($fullPath);
    }

    public function index()
    {
        $data = Magang::latest()->get()->map(function ($item) {

            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'nama_kampus' => $item->nama_kampus,
                'tgl_mulai' => $item->tgl_mulai_magang,
                'tgl_selesai' => $item->tgl_selesai_magang,
                'status_magang' => $item->status_magang,
                'sertifikat' => $item->sertifikat,
                'cv_magang' => $this->getCvUrl($item->cv_magang),
                'nda_file' => $this->getCvUrl($item->nda_file),
                'keterangan' => $item->keterangan,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nama_kampus' => 'required|string|max:255',

            'tgl_mulai_magang' => 'required|date',
            'tgl_selesai_magang' => 'required|date',

            'cv_magang' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'nda_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',

            'sertifikat' => 'nullable|in:Sudah menerima,Belum menerima',

            'keterangan' => 'nullable|string'
        ]);

        if ($request->hasFile('cv_magang')) {
            $validated['cv_magang'] = $request
                ->file('cv_magang')
                ->store('cv-magang', 'public');
        }

        if ($request->hasFile('nda_file')) {
            $validated['nda_file'] = $request
                ->file('nda_file')
                ->store('nda-file', 'public');
        }

        $validated['sertifikat'] = $validated['sertifikat'] ?? 'Belum menerima';

        $magang = Magang::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan.',
            'data' => $magang
        ], 201);
    }

    /**
     * SHOW
     */
    public function show($id)
    {
        $magang = Magang::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                ...$magang->toArray(),
                'cv_magang' => $this->getCvUrl($magang->cv_magang),
                'nda_file' => $this->getCvUrl($magang->nda_file)
            ]
        ]);
    }

    /**
     * UPDATE
     */
    public function update(Request $request, $id)
    {
        $magang = Magang::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nama_kampus' => 'required|string|max:255',

            'tgl_mulai_magang' => 'required|date',
            'tgl_selesai_magang' => 'required|date',

            'cv_magang' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'nda_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',

            'sertifikat' => 'required|in:Sudah menerima,Belum menerima',

            'keterangan' => 'nullable|string'
        ]);

        if ($request->hasFile('cv_magang')) {

            if ($magang->cv_magang && Storage::disk('public')->exists($magang->cv_magang)) {
                Storage::disk('public')->delete($magang->cv_magang);
            }

            $validated['cv_magang'] = $request
                ->file('cv_magang')
                ->store('cv-magang', 'public');
        }

        if ($request->hasFile('nda_file')) {

            if ($magang->nda_file && Storage::disk('public')->exists($magang->nda_file)) {
                Storage::disk('public')->delete($magang->nda_file);
            }

            $validated['nda_file'] = $request
                ->file('nda_file')
                ->store('nda-file', 'public');
        }

        $magang->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate.',
            'data' => $magang
        ]);
    }

    /**
     * UPLOAD NDA
     */
    public function uploadNda(Request $request, $id)
    {
        $magang = Magang::findOrFail($id);

        $request->validate([
            'nda_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
        ]);

        if ($magang->nda_file && Storage::disk('public')->exists($magang->nda_file)) {
            Storage::disk('public')->delete($magang->nda_file);
        }

        $path = $request->file('nda_file')->store('nda-file', 'public');
        $magang->update(['nda_file' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'File NDA berhasil diupload.',
            'data' => [
                'nda_file' => $this->getCvUrl($path)
            ]
        ]);
    }

    /**
     * DELETE
     */
    public function destroy($id)
    {
        $magang = Magang::findOrFail($id);

        if ($magang->cv_magang && Storage::disk('public')->exists($magang->cv_magang)) {
            Storage::disk('public')->delete($magang->cv_magang);
        }

        if ($magang->nda_file && Storage::disk('public')->exists($magang->nda_file)) {
            Storage::disk('public')->delete($magang->nda_file);
        }

        $magang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus.'
        ]);
    }
}