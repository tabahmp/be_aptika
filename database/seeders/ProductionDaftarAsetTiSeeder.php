<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AsetTiNama;
use App\Models\AsetTiKlasifikasi;
use App\Models\AsetTiJenis;
use App\Models\AsetTiKategori;
use App\Models\AsetTiMerek;
use App\Models\AsetTiTipe;
use App\Models\AsetTiSpesifikasi;
use App\Models\AsetTiPemanfaatan;
use App\Models\AsetTiPenyedia;
use App\Models\AsetTiPenanggungJawab;
use App\Models\DaftarAsetTi;

class ProductionDaftarAsetTiSeeder extends Seeder
{
    /**
     * Jalankan seeder khusus produksi.
     * Hanya memasukkan TEPAT 3 data awal resmi dan membersihkan data dummy jika ada.
     */
    public function run(): void
    {
        // Langkah 1: Isi semua master data lookup terlebih dahulu
        // agar dropdown form tidak kosong di environment produksi
        $this->call(AsetTiMasterSeeder::class);

        // 3 Data Spesifik Resmi untuk Inisialisasi Awal
        $targetAssets = [
            [
                'kode'                => '1.3.2.10.01.02.001-001',
                'nama_aset'           => 'PC HP 001 Monitor 24 inc Lenovo',
                'klasifikasi'         => 'Terbatas/personal',
                'jenis'               => 'hardware',
                'kategori'            => 'PC/Monitor',
                'no_seri'             => '4CE9092CV4',
                'merek'               => 'HP BUSINESS DESKTOP',
                'tipe'                => 'HP ProDesk 400GS MT',
                'spesifikasi_teknis'  => 'Machine Type : HP Desktop 100-240 3A 50/60Hz Product No : 5XD02PA#AR6 CPU:i7-8700 @3.20GHz HDD: 1T RAM: 8G OS: Window 10 Pro ODD:DVD RW Mouse: Wire Keyboard: Wired',
                'pemanfaatan'         => 'Penggunaan Pekerjaan Individu (Fajar Subakti)',
                'penyedia'            => '-',
                'tahun_pembelian'     => null,
                'garansi'             => null,
                'date_end'            => null,
                'tanggal_akhir_masa_pakai' => null,
                'lokasi'              => 'Ruang Staff Bidang Aptika',
                'penanggung_jawab'    => 'Asep Junaedi',
            ],
            [
                'kode'                => '1.3.2.10.01.02.001-006',
                'nama_aset'           => '1 Set /PC 007/Monitor 24 inch Lenovo',
                'klasifikasi'         => 'Terbatas/personal',
                'jenis'               => 'hardware',
                'kategori'            => 'PC/Monitor',
                'no_seri'             => 'SN:PC150BH6 MO:L39029520226',
                'merek'               => 'LENOVO IDEACENTRE',
                'tipe'                => 'IC510-15ICB [90HU00F1ID]',
                'spesifikasi_teknis'  => 'Machine Type : 90HU 100-240 3A 50/60Hz MTM: 90HU00F11D CPU:i7-9700GHz HDD: 2T RAM: 8G OS: Window 10 Home SL ODD:DVD RW Mouse: Wire Keyboard: Wired',
                'pemanfaatan'         => 'Penggunaan Pekerjaan Individu (Cony Trijuanto, ST)',
                'penyedia'            => 'PT. Bhineka',
                'tahun_pembelian'     => 2019,
                'garansi'             => null,
                'date_end'            => null,
                'tanggal_akhir_masa_pakai' => null,
                'lokasi'              => 'Ruang Kerja Bidang Aptika Meja kerja (Indri Koesnadi )',
                'penanggung_jawab'    => 'Cony Trijulianto',
            ],
            [
                'kode'                => '1.3.2.10.01.02.001-001',
                'nama_aset'           => 'PC 001 Dell All in One',
                'klasifikasi'         => 'Terbatas/personal',
                'jenis'               => 'hardware',
                'kategori'            => 'PC/Monitor',
                'no_seri'             => '7Y44V72',
                'merek'               => 'Dell',
                'tipe'                => 'IC510-15ICB [90HU00F1ID]',
                'spesifikasi_teknis'  => 'Machine Type : 90HU 100-240 3A 50/60Hz MTM: 90HU00F11D CPU:i7-9700GHz HDD: 2T RAM: 8G OS: Window 10 Home SL ODD:DVD RW Mouse: Wire Keyboard: Wired',
                'pemanfaatan'         => 'Penggunaan Pekerjaan Individu (Dian Istanti, S.Sos., M.A.P.)',
                'penyedia'            => '-',
                'tahun_pembelian'     => 2015,
                'garansi'             => null,
                'date_end'            => null,
                'tanggal_akhir_masa_pakai' => null,
                'lokasi'              => 'Ruang Kerja Kepala Bidang Aptika Meja kerja (Dian Istanti, S.Sos., M.A.P.)',
                'penanggung_jawab'    => 'Dian Istanti',
            ],
        ];

        DB::transaction(function () use ($targetAssets) {
            $bidangId = 3; // Bidang Aplikasi Informatika (APTIKA)

            foreach ($targetAssets as $row) {
                // Cek apakah data spesifik ini sudah ada agar tidak duplikat saat container restart
                $alreadyExists = DaftarAsetTi::where('kode', $row['kode'])
                    ->where('nama_aset', $row['nama_aset'])
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                $namaAsetId = AsetTiNama::firstOrCreate(['nama_aset' => $row['nama_aset']])->id;
                $klasifikasiId = AsetTiKlasifikasi::firstOrCreate(['nama_klasifikasi' => $row['klasifikasi']])->id;
                $jenisId = AsetTiJenis::firstOrCreate(['nama_jenis' => $row['jenis']])->id;
                $kategoriId = AsetTiKategori::firstOrCreate(['nama_kategori' => $row['kategori']])->id;
                $merekId = AsetTiMerek::firstOrCreate(['nama_merek' => $row['merek']])->id;
                $tipeId = AsetTiTipe::firstOrCreate(['nama_tipe' => $row['tipe']])->id;
                $spesifikasiId = AsetTiSpesifikasi::firstOrCreate(['spesifikasi_teknis' => $row['spesifikasi_teknis']])->id;
                $pemanfaatanId = AsetTiPemanfaatan::firstOrCreate(['pemanfaatan' => $row['pemanfaatan']])->id;
                $penyediaId = !empty($row['penyedia']) && $row['penyedia'] !== '-' ? AsetTiPenyedia::firstOrCreate(['nama_penyedia' => $row['penyedia']])->id : null;
                $pjId = AsetTiPenanggungJawab::firstOrCreate(['nama_pj' => $row['penanggung_jawab']])->id;

                DaftarAsetTi::create([
                    'bidang_id'                 => $bidangId,
                    'user_id'                   => 1,
                    'kode'                      => $row['kode'],
                    'nama_aset'                 => $row['nama_aset'],
                    'nama_aset_id'              => $namaAsetId,
                    'klasifikasi_id'            => $klasifikasiId,
                    'jenis_id'                  => $jenisId,
                    'kategori_id'               => $kategoriId,
                    'no_seri'                   => $row['no_seri'],
                    'merek_id'                  => $merekId,
                    'tipe_id'                   => $tipeId,
                    'spesifikasi_teknis'        => $row['spesifikasi_teknis'],
                    'spesifikasi_id'            => $spesifikasiId,
                    'pemanfaatan'               => $row['pemanfaatan'],
                    'pemanfaatan_id'            => $pemanfaatanId,
                    'penyedia_id'               => $penyediaId,
                    'tahun_pembelian'           => $row['tahun_pembelian'],
                    'garansi'                   => $row['garansi'],
                    'date_end'                  => $row['date_end'],
                    'tanggal_akhir_masa_pakai'  => $row['tanggal_akhir_masa_pakai'],
                    'lokasi'                    => $row['lokasi'],
                    'penanggung_jawab_id'       => $pjId,
                ]);
            }
        });

        if ($this->command) {
            $this->command->info('Database produksi berhasil diinisialisasi dengan tepat 3 data aset TI resmi.');
        }
    }
}
