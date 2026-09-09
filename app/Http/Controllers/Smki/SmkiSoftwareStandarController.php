<?php

namespace App\Http\Controllers\Smki;

use App\Http\Controllers\Controller;
use App\Models\SmkiKategori;
use App\Models\SmkiPenyediaBarang;
use App\Models\SmkiSoftwareStandar;
use App\Models\SmkiTipeSoftware;
use App\Services\SmkiDocxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmkiSoftwareStandarController extends Controller
{
    protected SmkiDocxService $docxService;

    public function __construct(SmkiDocxService $docxService)
    {
        $this->docxService = $docxService;
    }

    /**
     * Menampilkan daftar software standar dengan filter, paginasi, dan ringkasan KPI.
     */
    public function index(Request $request)
    {
        $query = SmkiSoftwareStandar::with(['kategori', 'tipeSoftware', 'penyediaBarang', 'user:id,name,email'])
            ->orderBy('nomor_kelompok', 'asc')
            ->orderBy('id', 'asc');

        // Filter Pencarian Teks
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_software', 'like', "%{$search}%")
                  ->orWhere('versi', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('kategori', fn($k) => $k->where('nama_kategori', 'like', "%{$search}%"))
                  ->orWhereHas('tipeSoftware', fn($t) => $t->where('nama_tipe_software', 'like', "%{$search}%"))
                  ->orWhereHas('penyediaBarang', fn($p) => $p->where('nama_penyedia_barang', 'like', "%{$search}%"));
            });
        }

        // Filter Kategori
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->input('kategori_id'));
        }

        // Filter Tipe Software
        if ($request->filled('tipe_software_id')) {
            $query->where('tipe_software_id', $request->input('tipe_software_id'));
        }

        // Filter Penyedia Barang
        if ($request->filled('penyedia_barang_id')) {
            $query->where('penyedia_barang_id', $request->input('penyedia_barang_id'));
        }

        // Paginasi vs All
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage === -1) {
            $items = $query->get();
            $paginated = null;
        } else {
            $paginated = $query->paginate($perPage);
            $items = $paginated->items();
        }

        // Hitung Ringkasan Statistik Global
        $baseStatsQuery = SmkiSoftwareStandar::query();
        $totalSoftware = (clone $baseStatsQuery)->count();

        $totalLisensi = (clone $baseStatsQuery)->whereHas('kategori', function ($k) {
            $k->where('nama_kategori', 'like', '%Lisensi%');
        })->count();

        $totalOpenSource = (clone $baseStatsQuery)->whereHas('kategori', function ($k) {
            $k->where('nama_kategori', 'like', '%Open source%');
        })->count();

        $totalInHouse = (clone $baseStatsQuery)->whereHas('kategori', function ($k) {
            $k->where('nama_kategori', 'like', '%In house%');
        })->count();

        $totalVendor = (clone $baseStatsQuery)->distinct('penyedia_barang_id')->whereNotNull('penyedia_barang_id')->count('penyedia_barang_id');

        return response()->json([
            'success' => true,
            'data'    => $items,
            'stats'   => [
                'total_software'   => $totalSoftware,
                'total_lisensi'    => $totalLisensi,
                'total_opensource' => $totalOpenSource,
                'total_inhouse'    => $totalInHouse,
                'total_vendor'     => $totalVendor,
            ],
            'meta' => $paginated ? [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ] : [
                'total' => count($items),
            ],
        ]);
    }

    /**
     * Mengambil data master dropdown lookup (Kategori, Tipe Software, Penyedia Barang).
     */
    public function lookup()
    {
        // Nomor kelompok yang sudah terpakai agar FE bisa menampilkan pilihan
        $nomorTerpakai = SmkiSoftwareStandar::whereNotNull('nomor_kelompok')
            ->distinct('nomor_kelompok')
            ->orderBy('nomor_kelompok')
            ->pluck('nomor_kelompok')
            ->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'kategoris'         => SmkiKategori::orderBy('nama_kategori')->get(),
                'tipe_softwares'    => SmkiTipeSoftware::orderBy('nama_tipe_software')->get(),
                'penyedia_barangs'  => SmkiPenyediaBarang::orderBy('nama_penyedia_barang')->get(),
                'nomor_terpakai'    => $nomorTerpakai,
            ],
        ]);
    }

    /**
     * Menyimpan data software standar baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_kelompok'      => 'nullable|integer|min:1',
            'nama_software'       => 'required|string|max:255',
            'versi'               => 'required|string|max:100',
            'kategori_id'         => 'required|exists:smki_kategoris,id',
            'tipe_software_id'    => 'required|exists:smki_tipe_softwares,id',
            'penyedia_barang_id'  => 'nullable|exists:smki_penyedia_barangs,id',
            'penyedia_baru'       => 'nullable|string|max:255',
            'keterangan'          => 'nullable|string',
        ]);

        // Jika user menginput nama penyedia baru
        if (empty($validated['penyedia_barang_id']) && !empty($validated['penyedia_baru'])) {
            $vendor = SmkiPenyediaBarang::firstOrCreate([
                'nama_penyedia_barang' => trim($validated['penyedia_baru']),
            ]);
            $validated['penyedia_barang_id'] = $vendor->id;
        }

        unset($validated['penyedia_baru']);
        $validated['user_id'] = auth()->id() ?? 1;

        $software = SmkiSoftwareStandar::create($validated);
        $software->load(['kategori', 'tipeSoftware', 'penyediaBarang', 'user:id,name,email']);

        return response()->json([
            'success' => true,
            'message' => 'Software standar berhasil ditambahkan ke inventaris SMKI.',
            'data'    => $software,
        ], 201);
    }

    /**
     * Menampilkan detail software standar.
     */
    public function show($id)
    {
        $software = SmkiSoftwareStandar::with(['kategori', 'tipeSoftware', 'penyediaBarang', 'user:id,name,email'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $software,
        ]);
    }

    /**
     * Mengubah data software standar.
     */
    public function update(Request $request, $id)
    {
        $software = SmkiSoftwareStandar::findOrFail($id);

        $validated = $request->validate([
            'nomor_kelompok'      => 'nullable|integer|min:1',
            'nama_software'       => 'sometimes|required|string|max:255',
            'versi'               => 'sometimes|required|string|max:100',
            'kategori_id'         => 'sometimes|required|exists:smki_kategoris,id',
            'tipe_software_id'    => 'sometimes|required|exists:smki_tipe_softwares,id',
            'penyedia_barang_id'  => 'nullable|exists:smki_penyedia_barangs,id',
            'penyedia_baru'       => 'nullable|string|max:255',
            'keterangan'          => 'nullable|string',
        ]);

        if (empty($validated['penyedia_barang_id']) && !empty($validated['penyedia_baru'])) {
            $vendor = SmkiPenyediaBarang::firstOrCreate([
                'nama_penyedia_barang' => trim($validated['penyedia_baru']),
            ]);
            $validated['penyedia_barang_id'] = $vendor->id;
        }

        unset($validated['penyedia_baru']);

        $software->update($validated);
        $software->load(['kategori', 'tipeSoftware', 'penyediaBarang', 'user:id,name,email']);

        return response()->json([
            'success' => true,
            'message' => 'Data software standar berhasil diperbarui.',
            'data'    => $software,
        ]);
    }

    /**
     * Menghapus data software standar (soft delete).
     */
    public function destroy($id)
    {
        $software = SmkiSoftwareStandar::findOrFail($id);
        $software->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data software standar berhasil dihapus dari inventaris SMKI.',
        ]);
    }

    /**
     * Ekspor data software standar ke template dokumen resmi FR-017 Daftar Software Standar.docx
     *
     * Query params opsional untuk header dokumen:
     *   - no_dokumen     : string  (default: nilai dari template)
     *   - no_revisi      : string  (default: nilai dari template)
     *   - tanggal_berlaku: string  (default: nilai dari template)
     */
    public function exportDocx(Request $request)
    {
        $query = SmkiSoftwareStandar::with(['kategori', 'tipeSoftware', 'penyediaBarang'])
            ->orderBy('nomor_kelompok', 'asc')
            ->orderBy('id', 'asc');

        // Jika ingin mengunduh hanya 1 record
        if ($request->filled('id')) {
            $query->where('id', $request->input('id'));
        } else {
            // Terapkan filter jika ada
            if ($request->filled('search')) {
                $search = trim($request->input('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('nama_software', 'like', "%{$search}%")
                      ->orWhere('versi', 'like', "%{$search}%")
                      ->orWhereHas('kategori', fn($k) => $k->where('nama_kategori', 'like', "%{$search}%"))
                      ->orWhereHas('tipeSoftware', fn($t) => $t->where('nama_tipe_software', 'like', "%{$search}%"))
                      ->orWhereHas('penyediaBarang', fn($p) => $p->where('nama_penyedia_barang', 'like', "%{$search}%"));
                });
            }

            if ($request->filled('kategori_id')) {
                $query->where('kategori_id', $request->input('kategori_id'));
            }

            if ($request->filled('tipe_software_id')) {
                $query->where('tipe_software_id', $request->input('tipe_software_id'));
            }

            if ($request->filled('penyedia_barang_id')) {
                $query->where('penyedia_barang_id', $request->input('penyedia_barang_id'));
            }
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data software standar yang dapat diekspor.',
            ], 404);
        }

        // Siapkan opsi header dokumen dari parameter request
        $options = [];
        if ($request->filled('no_dokumen')) {
            $options['no_dokumen'] = $request->input('no_dokumen');
        }
        if ($request->filled('no_revisi')) {
            $options['no_revisi'] = $request->input('no_revisi');
        }
        if ($request->filled('tanggal_berlaku')) {
            $options['tanggal_berlaku'] = $request->input('tanggal_berlaku');
        }

        $filePath = $this->docxService->generateDocx($items, $options);
        $filename = 'FR-017_Daftar_Software_Standar_' . date('Ymd_His') . '.docx';

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
