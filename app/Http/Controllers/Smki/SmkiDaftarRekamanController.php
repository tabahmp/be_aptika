<?php

namespace App\Http\Controllers\Smki;

use App\Http\Controllers\Controller;
use App\Models\SmkiDaftarRekaman;
use App\Models\SmkiRekamanKlasifikasi;
use App\Models\SmkiRekamanRetensi;
use App\Models\SmkiRekamanPemilik;
use App\Services\SmkiDaftarRekamanDocxService;
use Illuminate\Http\Request;

class SmkiDaftarRekamanController extends Controller
{
    protected SmkiDaftarRekamanDocxService $docxService;

    public function __construct(SmkiDaftarRekamanDocxService $docxService)
    {
        $this->docxService = $docxService;
    }
    /**
     * Display a listing of record items with filters, header info, and stats.
     */
    public function index(Request $request)
    {
        $query = SmkiDaftarRekaman::with(['klasifikasi', 'retensi', 'pemilik', 'user:id,name,email'])
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhereHas('klasifikasi', fn($k) => $k->where('nama_klasifikasi', 'like', "%{$search}%"))
                  ->orWhereHas('retensi', fn($r) => $r->where('nama_retensi', 'like', "%{$search}%"))
                  ->orWhereHas('pemilik', fn($p) => $p->where('nama_pemilik', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('klasifikasi_id')) {
            $query->where('klasifikasi_id', $request->input('klasifikasi_id'));
        }

        if ($request->filled('retensi_id')) {
            $query->where('retensi_id', $request->input('retensi_id'));
        }

        if ($request->filled('pemilik_id')) {
            $query->where('pemilik_id', $request->input('pemilik_id'));
        }

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage === -1) {
            $items = $query->get();
            $paginated = null;
        } else {
            $paginated = $query->paginate($perPage);
            $items = $paginated->items();
        }

        // Stats Global
        $baseStatsQuery = SmkiDaftarRekaman::query();
        $totalRekaman = (clone $baseStatsQuery)->count();
        $totalUmum = (clone $baseStatsQuery)->whereHas('klasifikasi', fn($k) => $k->where('nama_klasifikasi', 'like', '%Umum%'))->count();
        $totalTerbatas = (clone $baseStatsQuery)->whereHas('klasifikasi', fn($k) => $k->where('nama_klasifikasi', 'not like', '%Umum%'))->count();
        $totalPemilik = (clone $baseStatsQuery)->distinct('pemilik_id')->whereNotNull('pemilik_id')->count('pemilik_id');

        return response()->json([
            'success' => true,
            'header' => [
                'no_dokumen'      => 'FR-003/KOM.03.05/SANDIKAMI',
                'no_revisi'       => '1.0',
                'tanggal_berlaku' => '14 Oktober 2022',
            ],
            'stats' => [
                'total_rekaman'  => $totalRekaman,
                'total_umum'     => $totalUmum,
                'total_terbatas' => $totalTerbatas,
                'total_pemilik'  => $totalPemilik,
            ],
            'data'  => $items,
            'meta'  => $paginated ? [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ] : null,
        ]);
    }

    /**
     * Get lookup reference options for modal dropdowns.
     */
    public function lookup()
    {
        return response()->json([
            'success'      => true,
            'klasifikasi'  => SmkiRekamanKlasifikasi::select('id', 'nama_klasifikasi')->orderBy('nama_klasifikasi')->get(),
            'retensi'      => SmkiRekamanRetensi::select('id', 'nama_retensi')->orderByRaw('CAST(SUBSTRING_INDEX(nama_retensi, \' \', 1) AS UNSIGNED), nama_retensi')->get(),
            'pemilik'      => SmkiRekamanPemilik::select('id', 'nama_pemilik')->orderBy('nama_pemilik')->get(),
        ]);
    }

    /**
     * Export daftar rekaman ke template dokumen Word (.docx) FR-003.
     */
    public function exportDocx(Request $request)
    {
        try {
            $query = SmkiDaftarRekaman::with(['klasifikasi', 'retensi', 'pemilik']);

            if ($request->filled('id')) {
                $query->where('id', $request->input('id'));
            } else {
                if ($request->filled('search')) {
                    $search = trim($request->input('search'));
                    $query->where(function ($q) use ($search) {
                        $q->where('judul', 'like', "%{$search}%")
                          ->orWhereHas('klasifikasi', fn($k) => $k->where('nama_klasifikasi', 'like', "%{$search}%"))
                          ->orWhereHas('retensi', fn($r) => $r->where('nama_retensi', 'like', "%{$search}%"))
                          ->orWhereHas('pemilik', fn($p) => $p->where('nama_pemilik', 'like', "%{$search}%"));
                    });
                }

                if ($request->filled('klasifikasi_id')) {
                    $query->where('klasifikasi_id', $request->input('klasifikasi_id'));
                }

                if ($request->filled('retensi_id')) {
                    $query->where('retensi_id', $request->input('retensi_id'));
                }

                if ($request->filled('pemilik_id')) {
                    $query->where('pemilik_id', $request->input('pemilik_id'));
                }
            }

            $items = $query->orderBy('id', 'asc')->get();

            $options = [
                'no_dokumen'      => $request->input('no_dokumen', 'FR-003/KOM.03.05/SANDIKAMI'),
                'no_revisi'       => $request->input('no_revisi', '1.0'),
                'tanggal_berlaku' => $request->input('tanggal_berlaku', '14 Oktober 2022'),
            ];

            $docxPath = $this->docxService->generateDocx($items, $options);
            $fileName = 'FR-003_Daftar_Rekaman_' . date('Y-m-d') . '.docx';

            return response()->download($docxPath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'          => 'required|string|max:255',
            'klasifikasi_id' => 'nullable|exists:smki_rekaman_klasifikasis,id',
            'retensi_id'     => 'nullable|exists:smki_rekaman_retensis,id',
            'pemilik_id'     => 'nullable|exists:smki_rekaman_pemiliks,id',
            'nama_klasifikasi' => 'nullable|string|max:255',
            'nama_retensi'     => 'nullable|string|max:255',
            'nama_pemilik'     => 'nullable|string|max:255',
        ]);

        $klasifikasiId = $this->resolveLookup(
            $validated['klasifikasi_id'] ?? null,
            $validated['nama_klasifikasi'] ?? null,
            SmkiRekamanKlasifikasi::class,
            'nama_klasifikasi'
        );

        $retensiId = $this->resolveLookup(
            $validated['retensi_id'] ?? null,
            $validated['nama_retensi'] ?? null,
            SmkiRekamanRetensi::class,
            'nama_retensi'
        );

        $pemilikId = $this->resolveLookup(
            $validated['pemilik_id'] ?? null,
            $validated['nama_pemilik'] ?? null,
            SmkiRekamanPemilik::class,
            'nama_pemilik'
        );

        $rekaman = SmkiDaftarRekaman::create([
            'user_id'        => auth()->id(),
            'bidang_id'      => auth()->user()?->bidang_id,
            'judul'          => $validated['judul'],
            'klasifikasi_id' => $klasifikasiId,
            'retensi_id'     => $retensiId,
            'pemilik_id'     => $pemilikId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data rekaman berhasil ditambahkan.',
            'data'    => $rekaman->load(['klasifikasi', 'retensi', 'pemilik']),
        ], 201);
    }

    /**
     * Display the specified record.
     */
    public function show($id)
    {
        $rekaman = SmkiDaftarRekaman::with(['klasifikasi', 'retensi', 'pemilik', 'user:id,name,email'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $rekaman,
        ]);
    }

    /**
     * Update the specified record.
     */
    public function update(Request $request, $id)
    {
        $rekaman = SmkiDaftarRekaman::findOrFail($id);

        $validated = $request->validate([
            'judul'          => 'sometimes|required|string|max:255',
            'klasifikasi_id' => 'nullable|exists:smki_rekaman_klasifikasis,id',
            'retensi_id'     => 'nullable|exists:smki_rekaman_retensis,id',
            'pemilik_id'     => 'nullable|exists:smki_rekaman_pemiliks,id',
            'nama_klasifikasi' => 'nullable|string|max:255',
            'nama_retensi'     => 'nullable|string|max:255',
            'nama_pemilik'     => 'nullable|string|max:255',
        ]);

        if (array_key_exists('judul', $validated)) {
            $rekaman->judul = $validated['judul'];
        }

        if (array_key_exists('klasifikasi_id', $validated) || array_key_exists('nama_klasifikasi', $validated)) {
            $rekaman->klasifikasi_id = $this->resolveLookup(
                $validated['klasifikasi_id'] ?? null,
                $validated['nama_klasifikasi'] ?? null,
                SmkiRekamanKlasifikasi::class,
                'nama_klasifikasi'
            );
        }

        if (array_key_exists('retensi_id', $validated) || array_key_exists('nama_retensi', $validated)) {
            $rekaman->retensi_id = $this->resolveLookup(
                $validated['retensi_id'] ?? null,
                $validated['nama_retensi'] ?? null,
                SmkiRekamanRetensi::class,
                'nama_retensi'
            );
        }

        if (array_key_exists('pemilik_id', $validated) || array_key_exists('nama_pemilik', $validated)) {
            $rekaman->pemilik_id = $this->resolveLookup(
                $validated['pemilik_id'] ?? null,
                $validated['nama_pemilik'] ?? null,
                SmkiRekamanPemilik::class,
                'nama_pemilik'
            );
        }

        $rekaman->save();

        return response()->json([
            'success' => true,
            'message' => 'Data rekaman berhasil diperbarui.',
            'data'    => $rekaman->load(['klasifikasi', 'retensi', 'pemilik']),
        ]);
    }

    /**
     * Remove the specified record.
     */
    public function destroy($id)
    {
        $rekaman = SmkiDaftarRekaman::findOrFail($id);
        $rekaman->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data rekaman berhasil dihapus.',
        ]);
    }

    private function resolveLookup(?int $id, ?string $name, string $modelClass, string $columnName): ?int
    {
        if ($id) {
            return $id;
        }

        if (!empty($name)) {
            $existing = $modelClass::where($columnName, $name)->first();
            if ($existing) {
                return $existing->id;
            }
            $created = $modelClass::create([$columnName => $name]);
            return $created->id;
        }

        return null;
    }
}
