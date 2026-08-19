<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT USERS DARI 7 BIDANG BERBEDA
        $users = [
            [
                'id' => 1,
                'name' => 'Admin Aptika Utama',
                'email' => 'admin@aptika.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'bidang_id' => 3, // APTIKA
                'is_active' => 1,
                'position' => 'Administrator Utama Aptika',
                'phone' => '081234567890',
            ],
            [
                'id' => 2,
                'name' => 'Staf Aptika',
                'email' => 'user@aptika.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'bidang_id' => 3, // APTIKA
                'is_active' => 1,
                'position' => 'Pranata Komputer Aptika',
                'phone' => '081234567891',
            ],
            [
                'id' => 3,
                'name' => 'Budi Sekretariat',
                'email' => 'sekretariat@jabarprov.go.id',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'bidang_id' => 1, // SEKRETARIAT
                'is_active' => 1,
                'position' => 'Analis Tata Usaha',
                'phone' => '081234567892',
            ],
            [
                'id' => 4,
                'name' => 'Dewi E-Gov',
                'email' => 'egov@jabarprov.go.id',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'bidang_id' => 2, // EGOV
                'is_active' => 1,
                'position' => 'Analis SPBE E-Gov',
                'phone' => '081234567893',
            ],
            [
                'id' => 5,
                'name' => 'Eko IKP',
                'email' => 'ikp@jabarprov.go.id',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'bidang_id' => 4, // IKP
                'is_active' => 1,
                'position' => 'Pranata Humas IKP',
                'phone' => '081234567894',
            ],
            [
                'id' => 6,
                'name' => 'Fajri Persandian',
                'email' => 'persandian@jabarprov.go.id',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'bidang_id' => 5, // PERSANDIAN
                'is_active' => 1,
                'position' => 'Manggala Informatika Persandian',
                'phone' => '081234567895',
            ],
            [
                'id' => 7,
                'name' => 'Gita Statistik',
                'email' => 'statistik@jabarprov.go.id',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'bidang_id' => 6, // STATISTIK
                'is_active' => 1,
                'position' => 'Statistisi Pertama',
                'phone' => '081234567896',
            ],
            [
                'id' => 8,
                'name' => 'Hendra PLDDIG',
                'email' => 'plddig@jabarprov.go.id',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'bidang_id' => 7, // PLDDIG
                'is_active' => 1,
                'position' => 'Teknisi Jabar Digital Service',
                'phone' => '081234567897',
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['id' => $u['id']], $u);
        }

        // 2. DUMMY NOTA DINAS PER BIDANG
        $notaDinasList = [
            [
                'id' => 1,
                'nomor_surat' => 'ND-2026-001',
                'tujuan' => 'Kepala Dinas Kominfo Jabar',
                'dari' => 'Kepala Bidang Aptika',
                'tembusan' => 'Sekretaris Dinas',
                'sifat_surat' => 'Biasa',
                'perihal' => 'Pengembangan Fitur Multi-Bidang APTIKA Tools',
                'isi_surat' => 'Mohon persetujuan transformasi arsitektur multi-bidang untuk 7 unit kerja.',
                'tanggal_surat' => '2026-08-19',
                'status' => 'terkirim',
                'user_id' => 1,
                'bidang_id' => 3, // APTIKA
            ],
            [
                'id' => 2,
                'nomor_surat' => 'ND-2026-002',
                'tujuan' => 'Kepala Bidang E-Gov',
                'dari' => 'Staf E-Gov',
                'tembusan' => '-',
                'sifat_surat' => 'Penting',
                'perihal' => 'Evaluasi Arsitektur SPBE Jawa Barat 2026',
                'isi_surat' => 'Penyampaian draf evaluasi integrasi domain SPBE.',
                'tanggal_surat' => '2026-08-18',
                'status' => 'terkirim',
                'user_id' => 4,
                'bidang_id' => 2, // EGOV
            ],
            [
                'id' => 3,
                'nomor_surat' => 'ND-2026-003',
                'tujuan' => 'Kepala Bidang Persandian',
                'dari' => 'Tim CSIRT Jabar',
                'tembusan' => 'Kepala Dinas',
                'sifat_surat' => 'Rahasia',
                'perihal' => 'Laporan Audit Keamanan Cyber Triwulan III',
                'isi_surat' => 'Hasil pemeriksaan patch keamanan server internal.',
                'tanggal_surat' => '2026-08-17',
                'status' => 'draft',
                'user_id' => 6,
                'bidang_id' => 5, // PERSANDIAN
            ],
        ];

        foreach ($notaDinasList as $nd) {
            DB::table('nota_dinas')->updateOrInsert(['id' => $nd['id']], $nd);
        }

        // 3. DUMMY MAGANG PER BIDANG
        $magangList = [
            [
                'id' => 1,
                'nama' => 'Rizky Pratama',
                'nama_kampus' => 'Universitas Padjadjaran',
                'tgl_mulai_magang' => '2026-07-01',
                'tgl_selesai_magang' => '2026-09-30',
                'cv_magang' => 'cv-magang/dummy_rizky.pdf',
                'sertifikat' => 'Belum menerima',
                'keterangan' => 'Magang Software Engineering di Bidang Aptika',
                'bidang_id' => 3, // APTIKA
            ],
            [
                'id' => 2,
                'nama' => 'Siti Nurhaliza',
                'nama_kampus' => 'ITB',
                'tgl_mulai_magang' => '2026-06-15',
                'tgl_selesai_magang' => '2026-08-15',
                'cv_magang' => 'cv-magang/dummy_siti.pdf',
                'sertifikat' => 'Sudah menerima',
                'keterangan' => 'Magang Data Analyst di Bidang E-Gov',
                'bidang_id' => 2, // EGOV
            ],
            [
                'id' => 3,
                'nama' => 'Aditya Wijaya',
                'nama_kampus' => 'Universitas Telkom',
                'tgl_mulai_magang' => '2026-08-01',
                'tgl_selesai_magang' => '2026-10-31',
                'cv_magang' => 'cv-magang/dummy_aditya.pdf',
                'sertifikat' => 'Belum menerima',
                'keterangan' => 'Magang Cybersecurity Analyst di Bidang Persandian',
                'bidang_id' => 5, // PERSANDIAN
            ],
        ];

        foreach ($magangList as $m) {
            DB::table('magangs')->updateOrInsert(['id' => $m['id']], $m);
        }

        // 4. DUMMY MANAJEMEN TUGAS BOARDS PER BIDANG
        $boards = [
            [
                'id' => 1,
                'name' => 'Pengembangan Aptika Tools Multi-Bidang',
                'description' => 'Proyek modernisasi portal Aptika Tools untuk 7 unit kerja Diskominfo Jabar',
                'created_by' => 1,
                'status' => 'active',
                'visibility' => 'public',
                'bidang_id' => 3, // APTIKA
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Digitalisasi Layanan Publik SPBE E-Gov',
                'description' => 'Proyek integrasi portal aplikasi SPBE',
                'created_by' => 4,
                'status' => 'active',
                'visibility' => 'public',
                'bidang_id' => 2, // EGOV
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Pengembangan Fitur Jabar Super Apps (PLDDIG)',
                'description' => 'Proyek peningkatan performa Sapawarga Jabar',
                'created_by' => 8,
                'status' => 'active',
                'visibility' => 'public',
                'bidang_id' => 7, // PLDDIG
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($boards as $b) {
            DB::table('boards')->updateOrInsert(['id' => $b['id']], $b);
        }
    }
}
