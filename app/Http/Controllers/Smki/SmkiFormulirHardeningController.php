<?php

namespace App\Http\Controllers\Smki;

use App\Http\Controllers\Controller;
use App\Models\DaftarAsetTi;
use App\Models\SmkiFormulirHardening;
use App\Models\SmkiFormulirHardeningChecklist;
use App\Services\SmkiHardeningDocxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmkiFormulirHardeningController extends Controller
{
    protected SmkiHardeningDocxService $docxService;

    public function __construct(SmkiHardeningDocxService $docxService)
    {
        $this->docxService = $docxService;
    }

    /**
     * Standar 11 Item Checklist Hardening (FR-047 & ISO/IEC 27001)
     */
    public static function getDefaultChecklistTemplate(): array
    {
        return [
            // 1. Sistem Operasi
            [
                'kategori'        => 'Sistem Operasi',
                'item_pengecekan' => 'Sistem operasi telah diperbarui ke versi terbaru',
                'urutan'          => 1,
                'checklist'       => 'Pass',
                'keterangan'      => '',
            ],
            [
                'kategori'        => 'Sistem Operasi',
                'item_pengecekan' => 'Patch keamanan terkini sudah diinstal',
                'urutan'          => 2,
                'checklist'       => 'Pass',
                'keterangan'      => '',
            ],
            [
                'kategori'        => 'Sistem Operasi',
                'item_pengecekan' => 'Firewall telah aktif dan dikonfigurasi dengan benar',
                'urutan'          => 3,
                'checklist'       => 'Pass',
                'keterangan'      => '',
            ],

            // 2. Perlindungan Kata Sandi
            [
                'kategori'        => 'Perlindungan Kata Sandi',
                'item_pengecekan' => 'Menggunakan kata sandi yang kuat (panjang, kombinasi huruf besar, kecil, angka, simbol)',
                'urutan'          => 4,
                'checklist'       => 'Pass',
                'keterangan'      => '',
            ],
            [
                'kategori'        => 'Perlindungan Kata Sandi',
                'item_pengecekan' => 'Kata sandi admin dan akun pengguna default telah diubah',
                'urutan'          => 5,
                'checklist'       => 'Pass',
                'keterangan'      => '',
            ],

            // 3. Perangkat Lunak
            [
                'kategori'        => 'Perangkat Lunak',
                'item_pengecekan' => 'Perangkat lunak yang diinstal telah sesuai dengan whitelist software yang dimiliki',
                'urutan'          => 6,
                'checklist'       => 'Pass',
                'keterangan'      => '',
            ],
            [
                'kategori'        => 'Perangkat Lunak',
                'item_pengecekan' => 'Antivirus dan anti-malware telah diinstal dan diperbarui',
                'urutan'          => 7,
                'checklist'       => 'Pass',
                'keterangan'      => '',
            ],
            [
                'kategori'        => 'Perangkat Lunak',
                'item_pengecekan' => 'Pengaturan pembaruan otomatis untuk aplikasi telah diaktifkan',
                'urutan'          => 8,
                'checklist'       => 'Pass',
                'keterangan'      => '',
            ],

            // 4. Kontrol Akses
            [
                'kategori'        => 'Kontrol Akses',
                'item_pengecekan' => 'Hak akses pengguna dibatasi (tidak semua pengguna memiliki hak admin) sesuai dengan matriks akses yang dimiliki',
                'urutan'          => 9,
                'checklist'       => 'Pass',
                'keterangan'      => '',
            ],

            // 5. Keamanan Fisik
            [
                'kategori'        => 'Keamanan Fisik',
                'item_pengecekan' => 'Layar dikunci otomatis setelah 10 menit tidak digunakan',
                'urutan'          => 10,
                'checklist'       => 'Pass',
                'keterangan'      => '',
            ],

            // 6. Penyimpanan
            [
                'kategori'        => 'Penyimpanan',
                'item_pengecekan' => 'Batas maksimal penyimpanan 90% dari total ruang (storage) yang tersedia',
                'urutan'          => 11,
                'checklist'       => 'Pass',
                'keterangan'      => '',
            ],
        ];
    }

    /**
     * Menampilkan daftar formulir hardening dengan filter, paginasi, dan KPI summary.
     */
    public function index(Request $request)
    {
        $query = SmkiFormulirHardening::with(['aset', 'user:id,name,email'])
            ->orderBy('id', 'desc');

        // Filter Pencarian Teks
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('no_dokumen', 'like', "%{$search}%")
                  ->orWhere('nomor_aset', 'like', "%{$search}%")
                  ->orWhere('jenis_aset', 'like', "%{$search}%")
                  ->orWhere('merek_tipe', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('nama_auditor', 'like', "%{$search}%");
            });
        }

        // Filter Jenis Aset
        if ($request->filled('jenis_aset')) {
            $query->where('jenis_aset', $request->input('jenis_aset'));
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter Rentang Tanggal
        if ($request->filled('date_from')) {
            $query->where('tanggal_check', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('tanggal_check', '<=', $request->input('date_to'));
        }

        // Paginasi
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage === -1) {
            $items = $query->get();
            $paginated = null;
        } else {
            $paginated = $query->paginate($perPage);
            $items = $paginated->items();
        }

        // Ringkasan Statistik Global
        $baseQuery = SmkiFormulirHardening::query();
        $totalFormulir = (clone $baseQuery)->count();
        $totalSelesai  = (clone $baseQuery)->where('status', 'Selesai')->count();
        $totalProses   = (clone $baseQuery)->where('status', 'Dalam Proses')->count();
        $totalMenunggu = (clone $baseQuery)->where('status', 'Menunggu')->count();
        $totalDraft    = (clone $baseQuery)->where('status', 'Draft')->count();
        $avgCompliance = (clone $baseQuery)->avg('compliance_rate') ?: 0;

        return response()->json([
            'success' => true,
            'data'    => $items,
            'stats'   => [
                'total_formulir'   => $totalFormulir,
                'total_selesai'    => $totalSelesai,
                'total_proses'     => $totalProses,
                'total_menunggu'   => $totalMenunggu,
                'total_draft'      => $totalDraft,
                'avg_compliance'   => round($avgCompliance, 1),
            ],
            'meta' => $paginated ? [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ] : [
                'current_page' => 1,
                'last_page'    => 1,
                'per_page'     => count($items),
                'total'        => count($items),
            ],
        ]);
    }

    /**
     * Lookup master aset TI dan template default checklist.
     */
    public function lookup(Request $request)
    {
        // Ambil daftar aset TI untuk pilihan auto-complete
        $assets = DaftarAsetTi::with(['jenis', 'merek', 'tipe'])
            ->select('id', 'kode', 'nama_aset', 'nama_aset_id', 'jenis_id', 'merek_id', 'tipe_id', 'lokasi')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($a) {
                $merekName = $a->merek?->nama_merek ?: '';
                $tipeName  = $a->tipe?->nama_tipe ?: '';
                $combinedMerekTipe = trim("{$merekName} {$tipeName}");

                return [
                    'id'         => $a->id,
                    'nomor_aset' => $a->kode ?: "AST-" . str_pad($a->id, 3, '0', STR_PAD_LEFT),
                    'nama_aset'  => $a->nama_aset,
                    'jenis_aset' => $a->jenis?->nama_jenis ?: 'Laptop/PC',
                    'merek_tipe' => $combinedMerekTipe ?: ($a->nama_aset ?: 'Perangkat TI'),
                    'lokasi'     => $a->lokasi ?: 'Bidang APTIKA',
                ];
            });

        // Prediksi nomor dokumen berikutnya (pastikan belum pernah terpakai, termasuk yang soft-deleted)
        $nextNum = (SmkiFormulirHardening::withTrashed()->max('id') ?: 0) + 1;
        do {
            $suggestedNoDokumen = 'FR-047-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            $nextNum++;
        } while (SmkiFormulirHardening::withTrashed()->where('no_dokumen', $suggestedNoDokumen)->exists());

        return response()->json([
            'success' => true,
            'data'    => [
                'assets'               => $assets,
                'default_checklists'   => self::getDefaultChecklistTemplate(),
                'suggested_no_dokumen' => $suggestedNoDokumen,
                'status_options'       => ['Selesai', 'Dalam Proses', 'Menunggu', 'Draft'],
                'jenis_aset_options'   => ['Laptop/PC', 'Server', 'Router', 'Switch', 'Firewall', 'Storage', 'Lainnya'],
            ],
        ]);
    }

    /**
     * Simpan formulir hardening baru beserta 11 item checklist.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_dokumen'         => 'nullable|string|max:100|unique:smki_formulir_hardenings,no_dokumen',
            'no_revisi'          => 'nullable|string|max:50',
            'tanggal_terbit'     => 'nullable|date',
            'aset_id'            => 'nullable|integer|exists:daftar_aset_tis,id',
            'nomor_aset'         => 'required|string|max:150',
            'jenis_aset'         => 'nullable|string|max:100',
            'merek_tipe'         => 'nullable|string|max:200',
            'lokasi'             => 'nullable|string|max:250',
            'tanggal_check'      => 'nullable|date',
            'status'             => 'nullable|string|in:Selesai,Dalam Proses,Menunggu,Draft',
            'kota'               => 'nullable|string|max:100',
            'tanggal_pengesahan' => 'nullable|date',
            'nama_auditor'       => 'nullable|string|max:150',
            'nip_auditor'        => 'nullable|string|max:50',
            'jabatan_auditor'    => 'nullable|string|max:150',
            'checklists'         => 'required|array|min:1',
            'checklists.*.kategori'        => 'required|string',
            'checklists.*.item_pengecekan' => 'required|string',
            'checklists.*.urutan'          => 'nullable|integer',
            'checklists.*.checklist'       => 'required|string|in:Pass,Fail',
            'checklists.*.keterangan'      => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Auto generate No Dokumen jika tidak diinput
            if (empty($validated['no_dokumen'])) {
                $count = SmkiFormulirHardening::withTrashed()->max('id') ?: 0;
                do {
                    $count++;
                    $candidate = 'FR-047-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                } while (SmkiFormulirHardening::withTrashed()->where('no_dokumen', $candidate)->exists());
                $validated['no_dokumen'] = $candidate;
            }

            // Hitung ringkasan kepatuhan
            $checklistsData = $validated['checklists'];
            $total = count($checklistsData);
            $passed = 0;
            $failed = 0;

            foreach ($checklistsData as $c) {
                if ($c['checklist'] === 'Pass') {
                    $passed++;
                } else {
                    $failed++;
                }
            }

            $rate = $total > 0 ? round(($passed / $total) * 100, 2) : 0;

            $user = $request->user();
            $form = SmkiFormulirHardening::create([
                'no_dokumen'         => $validated['no_dokumen'],
                'no_revisi'          => $validated['no_revisi'] ?? '0.0',
                'tanggal_terbit'     => $validated['tanggal_terbit'] ?? now()->toDateString(),
                'aset_id'            => $validated['aset_id'] ?? null,
                'nomor_aset'         => $validated['nomor_aset'],
                'jenis_aset'         => $validated['jenis_aset'] ?? '-',
                'merek_tipe'         => $validated['merek_tipe'] ?? null,
                'lokasi'             => $validated['lokasi'] ?? null,
                'tanggal_check'      => $validated['tanggal_check'] ?? now()->toDateString(),
                'status'             => $validated['status'] ?? 'Draft',
                'kota'               => $validated['kota'] ?? 'Bandung',
                'tanggal_pengesahan' => $validated['tanggal_pengesahan'] ?? ($validated['tanggal_check'] ?? now()->toDateString()),
                'nama_auditor'       => $validated['nama_auditor'] ?? ($user?->name ?: 'Tim IT Security'),
                'nip_auditor'        => $validated['nip_auditor'] ?? null,
                'jabatan_auditor'    => $validated['jabatan_auditor'] ?? null,
                'compliance_rate'    => $rate,
                'items_passed'       => $passed,
                'items_failed'       => $failed,
                'bidang_id'          => $user?->bidang_id,
                'user_id'            => $user?->id,
            ]);

            foreach ($checklistsData as $idx => $item) {
                $form->checklists()->create([
                    'kategori'        => $item['kategori'],
                    'item_pengecekan' => $item['item_pengecekan'],
                    'urutan'          => $item['urutan'] ?? ($idx + 1),
                    'checklist'       => $item['checklist'],
                    'keterangan'      => $item['keterangan'] ?? null,
                ]);
            }

            $form->load(['checklists', 'aset']);

            return response()->json([
                'success' => true,
                'message' => 'Formulir Hardening berhasil disimpan.',
                'data'    => $form,
            ], 201);
        });
    }

    /**
     * Tampilkan detail formulir hardening beserta checklist.
     */
    public function show($id)
    {
        $form = SmkiFormulirHardening::with(['checklists', 'aset', 'user:id,name,email'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $form,
        ]);
    }

    /**
     * Update formulir hardening dan checklist.
     */
    public function update(Request $request, $id)
    {
        $form = SmkiFormulirHardening::findOrFail($id);

        $validated = $request->validate([
            'no_dokumen'         => "nullable|string|max:100|unique:smki_formulir_hardenings,no_dokumen,{$id}",
            'no_revisi'          => 'nullable|string|max:50',
            'tanggal_terbit'     => 'nullable|date',
            'aset_id'            => 'nullable|integer|exists:daftar_aset_tis,id',
            'nomor_aset'         => 'required|string|max:150',
            'jenis_aset'         => 'nullable|string|max:100',
            'merek_tipe'         => 'nullable|string|max:200',
            'lokasi'             => 'nullable|string|max:250',
            'tanggal_check'      => 'nullable|date',
            'status'             => 'nullable|string|in:Selesai,Dalam Proses,Menunggu,Draft',
            'kota'               => 'nullable|string|max:100',
            'tanggal_pengesahan' => 'nullable|date',
            'nama_auditor'       => 'nullable|string|max:150',
            'nip_auditor'        => 'nullable|string|max:50',
            'jabatan_auditor'    => 'nullable|string|max:150',
            'checklists'         => 'nullable|array',
            'checklists.*.id'              => 'nullable|integer',
            'checklists.*.kategori'        => 'required|string',
            'checklists.*.item_pengecekan' => 'required|string',
            'checklists.*.urutan'          => 'nullable|integer',
            'checklists.*.checklist'       => 'required|string|in:Pass,Fail',
            'checklists.*.keterangan'      => 'nullable|string',
        ]);

        return DB::transaction(function () use ($form, $validated) {
            $form->update([
                'no_dokumen'         => $validated['no_dokumen'] ?? $form->no_dokumen,
                'no_revisi'          => array_key_exists('no_revisi', $validated) ? $validated['no_revisi'] : $form->no_revisi,
                'tanggal_terbit'     => array_key_exists('tanggal_terbit', $validated) ? $validated['tanggal_terbit'] : $form->tanggal_terbit,
                'aset_id'            => $validated['aset_id'] ?? $form->aset_id,
                'nomor_aset'         => $validated['nomor_aset'],
                'jenis_aset'         => $validated['jenis_aset'] ?? $form->jenis_aset,
                'merek_tipe'         => $validated['merek_tipe'] ?? null,
                'lokasi'             => $validated['lokasi'] ?? null,
                'tanggal_check'      => $validated['tanggal_check'] ?? $form->tanggal_check,
                'status'             => $validated['status'] ?? $form->status,
                'kota'               => $validated['kota'] ?? $form->kota,
                'tanggal_pengesahan' => $validated['tanggal_pengesahan'] ?? $form->tanggal_pengesahan,
                'nama_auditor'       => $validated['nama_auditor'] ?? $form->nama_auditor,
                'nip_auditor'        => $validated['nip_auditor'] ?? $form->nip_auditor,
                'jabatan_auditor'    => $validated['jabatan_auditor'] ?? $form->jabatan_auditor,
            ]);

            // Sync checklist jika dikirimkan
            if (isset($validated['checklists'])) {
                // Hapus dan masukkan kembali agar urutan tetap sinkron
                $form->checklists()->delete();
                foreach ($validated['checklists'] as $idx => $item) {
                    $form->checklists()->create([
                        'kategori'        => $item['kategori'],
                        'item_pengecekan' => $item['item_pengecekan'],
                        'urutan'          => $item['urutan'] ?? ($idx + 1),
                        'checklist'       => $item['checklist'],
                        'keterangan'      => $item['keterangan'] ?? null,
                    ]);
                }
            }

            // Hitung ulang compliance rate
            $form->recalculateCompliance();
            $form->load(['checklists', 'aset']);

            return response()->json([
                'success' => true,
                'message' => 'Formulir Hardening berhasil diperbarui.',
                'data'    => $form,
            ]);
        });
    }

    /**
     * Hapus formulir hardening (soft delete).
     */
    public function destroy($id)
    {
        $form = SmkiFormulirHardening::findOrFail($id);
        $form->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Formulir Hardening berhasil dihapus.',
        ]);
    }

    /**
     * Ekspor dokumen Word resmi FR-047.
     */
    public function exportDocx($id)
    {
        $form = SmkiFormulirHardening::with(['checklists', 'aset'])
            ->findOrFail($id);

        try {
            $filePath = $this->docxService->generateDocx($form);
            $filename = "FR-047_Hardening_{$form->no_dokumen}.docx";

            return response()->download($filePath, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat dokumen FR-047: ' . $e->getMessage(),
            ], 500);
        }
    }
}
