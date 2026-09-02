<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MultiBidangUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Membuat 1 Admin dan 2 User untuk setiap dari 7 Bidang di Diskominfo Jawa Barat.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        $accounts = [
            // =========================================================================
            // 1. SEKRETARIAT (bidang_id: 1)
            // =========================================================================
            [
                'name'      => 'Admin Sekretariat',
                'email'     => 'admin@sekretariat.com',
                'role'      => 'admin',
                'bidang_id' => 1,
                'position'  => 'Admin Tata Usaha & Kepegawaian Sekretariat',
                'phone'     => '081234567011',
            ],
            [
                'name'      => 'Staf Sekretariat 1',
                'email'     => 'user1@sekretariat.com',
                'role'      => 'user',
                'bidang_id' => 1,
                'position'  => 'Pengadministrasi Perkantoran',
                'phone'     => '081234567012',
            ],
            [
                'name'      => 'Staf Sekretariat 2',
                'email'     => 'user2@sekretariat.com',
                'role'      => 'user',
                'bidang_id' => 1,
                'position'  => 'Pengelola Kepegawaian & SPD',
                'phone'     => '081234567013',
            ],

            // =========================================================================
            // 2. BIDANG E-GOVERNMENT (bidang_id: 2)
            // =========================================================================
            [
                'name'      => 'Admin E-Gov',
                'email'     => 'admin@egov.com',
                'role'      => 'admin',
                'bidang_id' => 2,
                'position'  => 'Admin Bidang E-Government / Tata Kelola SPBE',
                'phone'     => '081234567021',
            ],
            [
                'name'      => 'Staf E-Gov 1',
                'email'     => 'user1@egov.com',
                'role'      => 'user',
                'bidang_id' => 2,
                'position'  => 'Analis Tata Kelola SPBE',
                'phone'     => '081234567022',
            ],
            [
                'name'      => 'Staf E-Gov 2',
                'email'     => 'user2@egov.com',
                'role'      => 'user',
                'bidang_id' => 2,
                'position'  => 'Pengelola Infrastruktur SPBE',
                'phone'     => '081234567023',
            ],

            // =========================================================================
            // 3. BIDANG APLIKASI INFORMATIKA (bidang_id: 3 - Super Admin)
            // =========================================================================
            [
                'name'      => 'Admin Aptika',
                'email'     => 'admin@aptika.com',
                'role'      => 'admin',
                'bidang_id' => 3,
                'position'  => 'Super Administrator APTIKA Tools',
                'phone'     => '081234567031',
            ],
            [
                'name'      => 'Staf Aptika 1',
                'email'     => 'user@aptika.com',
                'role'      => 'user',
                'bidang_id' => 3,
                'position'  => 'Software Engineer / Pengembang Sistem',
                'phone'     => '081234567032',
            ],
            [
                'name'      => 'Staf Aptika 2',
                'email'     => 'user2@aptika.com',
                'role'      => 'user',
                'bidang_id' => 3,
                'position'  => 'Analis Aplikasi & Rekayasa Perangkat Lunak',
                'phone'     => '081234567033',
            ],

            // =========================================================================
            // 4. BIDANG INFORMASI & KOMUNIKASI PUBLIK (bidang_id: 4)
            // =========================================================================
            [
                'name'      => 'Admin IKP',
                'email'     => 'admin@ikp.com',
                'role'      => 'admin',
                'bidang_id' => 4,
                'position'  => 'Admin Bidang Informasi & Komunikasi Publik',
                'phone'     => '081234567041',
            ],
            [
                'name'      => 'Staf IKP 1',
                'email'     => 'user1@ikp.com',
                'role'      => 'user',
                'bidang_id' => 4,
                'position'  => 'Pengelola Saluran Komunikasi Publik',
                'phone'     => '081234567042',
            ],
            [
                'name'      => 'Staf IKP 2',
                'email'     => 'user2@ikp.com',
                'role'      => 'user',
                'bidang_id' => 4,
                'position'  => 'Pranata Humas & Peliputan Kedinasan',
                'phone'     => '081234567043',
            ],

            // =========================================================================
            // 5. BIDANG PERSANDIAN & KEAMANAN INFORMASI (bidang_id: 5)
            // =========================================================================
            [
                'name'      => 'Admin Persandian',
                'email'     => 'admin@persandian.com',
                'role'      => 'admin',
                'bidang_id' => 5,
                'position'  => 'Admin Persandian & Keamanan Informasi',
                'phone'     => '081234567051',
            ],
            [
                'name'      => 'Staf Persandian 1',
                'email'     => 'user1@persandian.com',
                'role'      => 'user',
                'bidang_id' => 5,
                'position'  => 'Security Analyst & Auditor SMKI',
                'phone'     => '081234567052',
            ],
            [
                'name'      => 'Staf Persandian 2',
                'email'     => 'user2@persandian.com',
                'role'      => 'user',
                'bidang_id' => 5,
                'position'  => 'Penetration Tester & Incident Responder',
                'phone'     => '081234567053',
            ],

            // =========================================================================
            // 6. BIDANG STATISTIK (bidang_id: 6)
            // =========================================================================
            [
                'name'      => 'Admin Statistik',
                'email'     => 'admin@statistik.com',
                'role'      => 'admin',
                'bidang_id' => 6,
                'position'  => 'Admin Bidang Statistik & Satu Data Jabar',
                'phone'     => '081234567061',
            ],
            [
                'name'      => 'Staf Statistik 1',
                'email'     => 'user1@statistik.com',
                'role'      => 'user',
                'bidang_id' => 6,
                'position'  => 'Data Analyst & Pengolah Data Sektoral',
                'phone'     => '081234567062',
            ],
            [
                'name'      => 'Staf Statistik 2',
                'email'     => 'user2@statistik.com',
                'role'      => 'user',
                'bidang_id' => 6,
                'position'  => 'Pengolah Metadata Statistik Daerah',
                'phone'     => '081234567063',
            ],

            // =========================================================================
            // 7. UPTD PUSAT LAYANAN DIGITAL / JDS (bidang_id: 7)
            // =========================================================================
            [
                'name'      => 'Admin PLDDIG',
                'email'     => 'admin@plddig.com',
                'role'      => 'admin',
                'bidang_id' => 7,
                'position'  => 'Admin UPTD Pusat Layanan Digital (JDS)',
                'phone'     => '081234567071',
            ],
            [
                'name'      => 'Staf PLDDIG 1',
                'email'     => 'user1@plddig.com',
                'role'      => 'user',
                'bidang_id' => 7,
                'position'  => 'Product Manager Jabar Super Apps (Sapawarga)',
                'phone'     => '081234567072',
            ],
            [
                'name'      => 'Staf PLDDIG 2',
                'email'     => 'user2@plddig.com',
                'role'      => 'user',
                'bidang_id' => 7,
                'position'  => 'UI/UX Researcher & Developer',
                'phone'     => '081234567073',
            ],
        ];

        foreach ($accounts as $acc) {
            User::updateOrCreate(
                ['email' => $acc['email']],
                [
                    'name'       => $acc['name'],
                    'password'   => $defaultPassword,
                    'role'       => $acc['role'],
                    'bidang_id'  => $acc['bidang_id'],
                    'position'   => $acc['position'],
                    'phone'      => $acc['phone'],
                    'is_active'  => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
