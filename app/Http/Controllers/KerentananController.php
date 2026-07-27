<?php

namespace App\Http\Controllers;

use App\Models\Kerentanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KerentananController extends Controller
{
    public function index(Request $request)
    {
        $query = Kerentanan::latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                  ->orWhere('aplikasi', 'like', "%{$search}%")
                  ->orWhere('tingkat_kerentanan', 'like', "%{$search}%")
                  ->orWhere('perihal', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginated = $query->paginate($perPage);

        // Append full URL for lampiran
        $items = collect($paginated->items())->map(function ($item) {
            if ($item->lampiran) {
                $item->lampiran_url = Storage::disk('public')->url($item->lampiran);
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date',
            'aplikasi' => 'nullable|string',
            'url' => 'nullable|string',
            'tingkat_kerentanan' => 'nullable|string',
            'perihal' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'isi_lampiran' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
            'status' => 'nullable|string',
            'lampiran' => 'nullable|file|max:10240', // Max 10MB
        ]);

        $validated['nomor_surat'] = Kerentanan::generateNomorSurat();
        $validated['tanggal'] = $validated['tanggal'] ?? now()->toDateString();
        $validated['perihal'] = $validated['perihal'] ?? 'Pemberitahuan Kerentanan Keamanan';
        $validated['aplikasi'] = $validated['aplikasi'] ?? 'Aplikasi Pemprov Jabar';
        $validated['tingkat_kerentanan'] = $validated['tingkat_kerentanan'] ?? 'Sedang';
        $validated['status'] = $validated['status'] ?? 'DRAF';

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $path = $file->store('kerentanan_lampiran', 'public');
            $validated['lampiran'] = $path;
            $validated['lampiran_nama'] = $file->getClientOriginalName();
        }

        $item = Kerentanan::create($validated);

        // Append lampiran_url
        if ($item->lampiran) {
            $item->lampiran_url = Storage::disk('public')->url($item->lampiran);
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan Pemberitahuan Kerentanan berhasil dibuat',
            'data' => $item,
        ], 201);
    }

    public function show($id)
    {
        $item = Kerentanan::findOrFail($id);

        if ($item->lampiran) {
            $item->lampiran_url = Storage::disk('public')->url($item->lampiran);
        }

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = Kerentanan::findOrFail($id);

        $validated = $request->validate([
            'tanggal' => 'nullable|date',
            'aplikasi' => 'nullable|string',
            'url' => 'nullable|string',
            'tingkat_kerentanan' => 'nullable|string',
            'perihal' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'isi_lampiran' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
            'status' => 'nullable|string',
            'lampiran' => 'nullable|file|max:10240',
            'hapus_lampiran' => 'nullable|boolean',
        ]);

        // Handle file removal
        if ($request->input('hapus_lampiran')) {
            if ($item->lampiran && Storage::disk('public')->exists($item->lampiran)) {
                Storage::disk('public')->delete($item->lampiran);
            }
            $validated['lampiran'] = null;
            $validated['lampiran_nama'] = null;
        }

        // Handle file upload
        if ($request->hasFile('lampiran')) {
            // Delete old file if exists
            if ($item->lampiran && Storage::disk('public')->exists($item->lampiran)) {
                Storage::disk('public')->delete($item->lampiran);
            }
            $file = $request->file('lampiran');
            $path = $file->store('kerentanan_lampiran', 'public');
            $validated['lampiran'] = $path;
            $validated['lampiran_nama'] = $file->getClientOriginalName();
        }

        unset($validated['hapus_lampiran']);
        $item->update($validated);

        if ($item->lampiran) {
            $item->lampiran_url = Storage::disk('public')->url($item->lampiran);
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan Pemberitahuan Kerentanan berhasil diperbarui',
            'data' => $item,
        ]);
    }

    public function destroy($id)
    {
        $item = Kerentanan::findOrFail($id);

        // Delete attached file if exists
        if ($item->lampiran && Storage::disk('public')->exists($item->lampiran)) {
            Storage::disk('public')->delete($item->lampiran);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan Pemberitahuan Kerentanan berhasil dihapus',
        ]);
    }

    public function export(Request $request)
    {
        $query = Kerentanan::latest();
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $items = $query->get();

        $filename = 'pemberitahuan_kerentanan_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($items) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['ID', 'Nomor Surat', 'Tanggal', 'Aplikasi', 'URL', 'Tingkat Kerentanan', 'Perihal', 'Lampiran', 'Status']);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->nomor_surat,
                    $item->tanggal ? $item->tanggal->format('Y-m-d') : '-',
                    $item->aplikasi,
                    $item->url,
                    $item->tingkat_kerentanan,
                    $item->perihal,
                    $item->lampiran_nama ?? '-',
                    $item->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

