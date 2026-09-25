<?php

namespace Database\Seeders;

use App\Models\BeritaAcara;
use App\Models\DetailMedia;
use App\Models\User;
use Illuminate\Database\Seeder;

class BeritaAcaraSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan ada user acuan jika belum ada
        $userPelaksana = User::firstOrCreate(
            ['email' => 'ahmad.fauzi@jabarprov.go.id'],
            [
                'name'      => 'Ahmad Fauzi',
                'password'  => bcrypt('password123'),
                'position'  => 'Pranata Komputer Pertama',
                'role'      => 'user',
                'is_active' => 1,
                'bidang_id' => 3,
            ]
        );

        $userDiketahui = User::firstOrCreate(
            ['email' => 'budi.santoso@jabarprov.go.id'],
            [
                'name'      => 'Budi Santoso',
                'password'  => bcrypt('password123'),
                'position'  => 'Kepala Seksi Keamanan Informasi',
                'role'      => 'admin',
                'is_active' => 1,
                'bidang_id' => 3,
            ]
        );

        $sampleReports = [
            [
                'nomor_dokumen'        => 'BA-001/SMKI/2025',
                'tanggal_pelaksanaan'  => '2025-09-12',
                'alasan_penghancuran'  => 'Media/perangkat tersebut dilakukan penghancuran atau disposal karena mengalami kerusakan, sudah tidak digunakan, dan sebagian data di dalamnya sudah tidak diperlukan. Kegiatan penghancuran dilakukan untuk mencegah penggunaan kembali perangkat serta mengurangi risiko akses terhadap informasi yang tersimpan pada media.',
                'id_pelaksana'         => $userPelaksana->id,
                'id_diketahui'         => $userDiketahui->id,
                'nama_pelaksana'       => 'Ahmad Fauzi',
                'nama_diketahui'       => 'Budi Santoso',
                'bidang_id'            => 3,
                'created_at'           => '2025-09-12 09:30:00',
                'media' => [
                    [
                        'nama_perangkat' => 'Hard Disk Internal',
                        'spesifikasi'    => 'Seagate Barracuda 1 TB',
                        'jenis_media'    => 'Storage',
                        'serial_number'  => 'SG-2021-001',
                        'jumlah'         => 2,
                        'satuan'         => 'Unit',
                        'keterangan'     => 'Rusak dan tidak dapat digunakan',
                    ],
                    [
                        'nama_perangkat' => 'Flashdisk',
                        'spesifikasi'    => 'SanDisk 32 GB',
                        'jenis_media'    => 'Storage',
                        'serial_number'  => 'SD-2022-014',
                        'jumlah'         => 5,
                        'satuan'         => 'Unit',
                        'keterangan'     => 'Tidak terbaca dan mengalami kerusakan',
                    ],
                    [
                        'nama_perangkat' => 'SSD Internal',
                        'spesifikasi'    => 'Kingston 240 GB',
                        'jenis_media'    => 'Storage',
                        'serial_number'  => 'KS-2020-008',
                        'jumlah'         => 1,
                        'satuan'         => 'Unit',
                        'keterangan'     => 'Kerusakan sistem penyimpanan',
                    ],
                ],
            ],
            [
                'nomor_dokumen'        => 'BA-002/SMKI/2025',
                'tanggal_pelaksanaan'  => '2025-09-11',
                'alasan_penghancuran'  => 'Kerusakan perangkat keras yang tidak dapat diperbaiki serta komponen internal terbakar.',
                'id_pelaksana'         => null,
                'id_diketahui'         => null,
                'nama_pelaksana'       => 'Siti Aminah',
                'nama_diketahui'       => 'Rudi Hartono',
                'bidang_id'            => 3,
                'created_at'           => '2025-09-10 14:15:00',
                'media' => [
                    [
                        'nama_perangkat' => 'Harddisk Eksternal',
                        'spesifikasi'    => 'WD My Passport 2 TB',
                        'jenis_media'    => 'Storage',
                        'serial_number'  => 'WD-2019-992',
                        'jumlah'         => 2,
                        'satuan'         => 'Unit',
                        'keterangan'     => 'Bad sector parah dan magnetic head putus',
                    ],
                ],
            ],
            [
                'nomor_dokumen'        => 'BA-003/SMKI/2025',
                'tanggal_pelaksanaan'  => '2025-09-09',
                'alasan_penghancuran'  => 'Penggantian perangkat usang (End of Life) yang telah habis masa susutnya.',
                'id_pelaksana'         => null,
                'id_diketahui'         => null,
                'nama_pelaksana'       => 'Andi Pratama',
                'nama_diketahui'       => 'Dewi Lestari',
                'bidang_id'            => 3,
                'created_at'           => '2025-09-08 11:00:00',
                'media' => [
                    [
                        'nama_perangkat' => 'Laptop Lama',
                        'spesifikasi'    => 'Lenovo ThinkPad X240',
                        'jenis_media'    => 'Laptop',
                        'serial_number'  => 'LN-2015-110',
                        'jumlah'         => 1,
                        'satuan'         => 'Unit',
                        'keterangan'     => 'Motherboard rusak dan baterai kembung',
                    ],
                ],
            ],
            [
                'nomor_dokumen'        => 'BA-004/SMKI/2025',
                'tanggal_pelaksanaan'  => '2025-09-06',
                'alasan_penghancuran'  => 'Data tidak sesuai dan telah dipindahkan ke arsip digital permanen.',
                'id_pelaksana'         => null,
                'id_diketahui'         => null,
                'nama_pelaksana'       => 'Rina Wijaya',
                'nama_diketahui'       => 'Agus Setiawan',
                'bidang_id'            => 3,
                'created_at'           => '2025-09-05 10:20:00',
                'media' => [
                    [
                        'nama_perangkat' => 'Flashdisk USB',
                        'spesifikasi'    => 'Kingston DataTraveler 16 GB',
                        'jenis_media'    => 'Storage',
                        'serial_number'  => 'DT-2018-055',
                        'jumlah'         => 3,
                        'satuan'         => 'Unit',
                        'keterangan'     => 'Konektor patah dan chip korosi',
                    ],
                ],
            ],
            [
                'nomor_dokumen'        => 'BA-005/SMKI/2025',
                'tanggal_pelaksanaan'  => '2025-09-02',
                'alasan_penghancuran'  => 'Penghapusan berkala sesuai SOP Siklus Hidup Media Keamanan Informasi.',
                'id_pelaksana'         => null,
                'id_diketahui'         => null,
                'nama_pelaksana'       => 'Taufik Hidayat',
                'nama_diketahui'       => 'Sari Dewi',
                'bidang_id'            => 3,
                'created_at'           => '2025-09-01 08:45:00',
                'media' => [
                    [
                        'nama_perangkat' => 'Magnetic Backup Tape',
                        'spesifikasi'    => 'LTO Ultrium 6 2.5TB',
                        'jenis_media'    => 'Storage',
                        'serial_number'  => 'LTO-2016-004',
                        'jumlah'         => 4,
                        'satuan'         => 'Unit',
                        'keterangan'     => 'Masa retensi terlampaui dan pita tergores',
                    ],
                ],
            ],
        ];

        foreach ($sampleReports as $rep) {
            $media = $rep['media'];
            unset($rep['media']);

            $existing = BeritaAcara::where('nomor_dokumen', $rep['nomor_dokumen'])->first();
            if (!$existing) {
                $ba = BeritaAcara::create($rep);
                $noUrut = 1;
                foreach ($media as $m) {
                    DetailMedia::create(array_merge($m, [
                        'id_pelaksanaan' => $ba->id_ba,
                        'no_urut'        => $noUrut++,
                    ]));
                }
            }
        }
    }
}
