<?php

namespace Database\Seeders;

use App\Http\Controllers\Smki\SmkiFormulirHardeningController;
use App\Models\DaftarAsetTi;
use App\Models\SmkiFormulirHardening;
use Illuminate\Database\Seeder;

class SmkiFormulirHardeningSeeder extends Seeder
{
    public function run(): void
    {
        if (SmkiFormulirHardening::count() > 0) {
            return;
        }

        $template = SmkiFormulirHardeningController::getDefaultChecklistTemplate();
        $sampleAset = DaftarAsetTi::first();

        // 1. Data Mockup Page 2 & 5: FR-047-0001 (Selesai, 92% Compliance)
        $form1 = SmkiFormulirHardening::create([
            'no_dokumen'         => 'FR-047-0001',
            'aset_id'            => $sampleAset?->id,
            'nomor_aset'         => $sampleAset?->kode ?: 'AST-PC-001',
            'jenis_aset'         => 'Laptop/PC',
            'merek_tipe'         => 'Dell Latitude 7420',
            'lokasi'             => 'Bandung, Lantai 3 Bidang APTIKA',
            'tanggal_check'      => '2026-09-02',
            'status'             => 'Selesai',
            'kota'               => 'Bandung',
            'tanggal_pengesahan' => '2026-09-02',
            'nama_auditor'       => 'Jijah',
            'nip_auditor'        => '19920815 202012 2 001',
            'jabatan_auditor'    => 'Administrator IT / IT Security',
            'compliance_rate'    => 90.91,
            'items_passed'       => 10,
            'items_failed'       => 1,
        ]);

        foreach ($template as $idx => $item) {
            // Beri 1 item fail sebagai variasi
            $checklist = ($idx === 3) ? 'Fail' : 'Pass';
            $keterangan = match ($idx) {
                0 => 'Windows 11 Enterprise 23H2 terpasang resmi',
                1 => 'Patch pembaruan keamanan 15/08/2026 sudah aktif',
                2 => 'Domain profile Windows Firewall active',
                3 => 'Password default belum diperbarui oleh pengguna (Perlu perbaikan)',
                4 => 'LAPS unique password aktif',
                5 => 'Standard Whitelist Software Diskominfo Jabar',
                6 => 'Defender definitions up to date',
                7 => 'Auto update aplikasi aktif via Microsoft Store',
                8 => 'Akses administrator dibatasi',
                9 => 'Screen saver lock 10 menit aktif',
                10 => 'Penyimpanan terpakai 45%, kapasitas aman',
                default => 'Compliant',
            };

            $form1->checklists()->create([
                'kategori'        => $item['kategori'],
                'item_pengecekan' => $item['item_pengecekan'],
                'urutan'          => $idx + 1,
                'checklist'       => $checklist,
                'keterangan'      => $keterangan,
            ]);
        }
        $form1->recalculateCompliance();

        // 2. Data Mockup 2: FR-047-0002 (Dalam Proses, Server HP ProLiant)
        $form2 = SmkiFormulirHardening::create([
            'no_dokumen'         => 'FR-047-0002',
            'aset_id'            => null,
            'nomor_aset'         => 'AST-SRV-002',
            'jenis_aset'         => 'Server',
            'merek_tipe'         => 'HP ProLiant DL360 Gen10',
            'lokasi'             => 'Data Center Diskominfo Jabar',
            'tanggal_check'      => '2026-09-01',
            'status'             => 'Dalam Proses',
            'kota'               => 'Bandung',
            'tanggal_pengesahan' => '2026-09-01',
            'nama_auditor'       => 'Tim IT Security',
            'nip_auditor'        => '19880520 201503 1 002',
            'jabatan_auditor'    => 'Security Engineer',
            'compliance_rate'    => 100.00,
            'items_passed'       => 11,
            'items_failed'       => 0,
        ]);

        foreach ($template as $idx => $item) {
            $form2->checklists()->create([
                'kategori'        => $item['kategori'],
                'item_pengecekan' => $item['item_pengecekan'],
                'urutan'          => $idx + 1,
                'checklist'       => 'Pass',
                'keterangan'      => 'Sesuai standar operasional server',
            ]);
        }
        $form2->recalculateCompliance();

        // 3. Data Mockup 3: FR-047-0003 (Menunggu, Router Cisco)
        $form3 = SmkiFormulirHardening::create([
            'no_dokumen'         => 'FR-047-0003',
            'aset_id'            => null,
            'nomor_aset'         => 'AST-NET-005',
            'jenis_aset'         => 'Router',
            'merek_tipe'         => 'Cisco ISR 4331',
            'lokasi'             => 'Ruang Server Jaringan',
            'tanggal_check'      => '2026-08-31',
            'status'             => 'Menunggu',
            'kota'               => 'Bandung',
            'tanggal_pengesahan' => '2026-08-31',
            'nama_auditor'       => 'Network Security Team',
            'nip_auditor'        => null,
            'jabatan_auditor'    => 'Auditor Infrastruktur',
            'compliance_rate'    => 81.82,
            'items_passed'       => 9,
            'items_failed'       => 2,
        ]);

        foreach ($template as $idx => $item) {
            $checklist = ($idx === 6 || $idx === 7) ? 'Fail' : 'Pass';
            $form3->checklists()->create([
                'kategori'        => $item['kategori'],
                'item_pengecekan' => $item['item_pengecekan'],
                'urutan'          => $idx + 1,
                'checklist'       => $checklist,
                'keterangan'      => $checklist === 'Pass' ? 'Checklist valid' : 'Menunggu update patch firmware',
            ]);
        }
        $form3->recalculateCompliance();
    }
}
