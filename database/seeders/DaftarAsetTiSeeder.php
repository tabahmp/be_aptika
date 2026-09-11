<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
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

class DaftarAsetTiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = __DIR__ . '/aset_data.json';
        if (!File::exists($jsonPath)) {
            $this->command->error("File aset_data.json tidak ditemukan.");
            return;
        }

        $items = json_decode(File::get($jsonPath), true);
        if (!$items) {
            $this->command->error("Gagal membaca aset_data.json.");
            return;
        }

        // Default bidang APTIKA id = 3
        $bidangId = 3;

        foreach ($items as $row) {
            $namaAsetStr = trim($row['nama_aset'] ?? '');
            if (empty($namaAsetStr)) continue;

            $namaAsetId = null;
            if (!empty($namaAsetStr)) {
                $namaAsetId = AsetTiNama::firstOrCreate(['nama_aset' => $namaAsetStr])->id;
            }

            $klasifikasiId = null;
            $klasifikasiStr = trim($row['klasifikasi'] ?? '');
            if (!empty($klasifikasiStr)) {
                $klasifikasiId = AsetTiKlasifikasi::firstOrCreate(['nama_klasifikasi' => $klasifikasiStr])->id;
            }

            $jenisId = null;
            $jenisStr = trim($row['jenis'] ?? '');
            if (!empty($jenisStr)) {
                $jenisId = AsetTiJenis::firstOrCreate(['nama_jenis' => $jenisStr])->id;
            }

            $kategoriId = null;
            $kategoriStr = trim($row['kategori'] ?? '');
            if (!empty($kategoriStr)) {
                $kategoriId = AsetTiKategori::firstOrCreate(['nama_kategori' => $kategoriStr])->id;
            }

            $merekId = null;
            $merekStr = trim($row['merek'] ?? '');
            if (!empty($merekStr)) {
                $merekId = AsetTiMerek::firstOrCreate(['nama_merek' => $merekStr])->id;
            }

            $tipeId = null;
            $tipeStr = trim($row['tipe'] ?? '');
            if (!empty($tipeStr)) {
                $tipeId = AsetTiTipe::firstOrCreate(['nama_tipe' => $tipeStr])->id;
            }

            $spesifikasiId = null;
            $spesifikasiStr = trim($row['spesifikasi_teknis'] ?? '');
            if (!empty($spesifikasiStr)) {
                $spesifikasiId = AsetTiSpesifikasi::firstOrCreate(['spesifikasi_teknis' => $spesifikasiStr])->id;
            }

            $pemanfaatanId = null;
            $pemanfaatanStr = trim($row['pemanfaatan'] ?? '');
            if (!empty($pemanfaatanStr)) {
                $pemanfaatanId = AsetTiPemanfaatan::firstOrCreate(['pemanfaatan' => $pemanfaatanStr])->id;
            }

            $penyediaId = null;
            $penyediaStr = trim($row['penyedia'] ?? '');
            if (!empty($penyediaStr)) {
                $penyediaId = AsetTiPenyedia::firstOrCreate(['nama_penyedia' => $penyediaStr])->id;
            }

            $pjId = null;
            $pjStr = trim($row['penanggung_jawab'] ?? '');
            if (!empty($pjStr)) {
                $pjId = AsetTiPenanggungJawab::firstOrCreate(['nama_pj' => $pjStr])->id;
            }

            DaftarAsetTi::updateOrCreate(
                [
                    'kode'      => !empty($row['kode']) && $row['kode'] !== '-' ? trim($row['kode']) : null,
                    'nama_aset' => $namaAsetStr,
                    'no_seri'   => !empty($row['no_seri']) ? trim($row['no_seri']) : null,
                ],
                [
                    'bidang_id'                => $bidangId,
                    'user_id'                  => 1,
                    'nama_aset_id'             => $namaAsetId,
                    'klasifikasi_id'           => $klasifikasiId,
                    'jenis_id'                 => $jenisId,
                    'kategori_id'              => $kategoriId,
                    'merek_id'                 => $merekId,
                    'tipe_id'                  => $tipeId,
                    'spesifikasi_id'           => $spesifikasiId,
                    'spesifikasi_teknis'       => $spesifikasiStr ?: null,
                    'pemanfaatan_id'           => $pemanfaatanId,
                    'pemanfaatan'              => $pemanfaatanStr ?: null,
                    'penyedia_id'              => $penyediaId,
                    'tahun_pembelian'          => !empty($row['tahun_pembelian']) ? trim($row['tahun_pembelian']) : null,
                    'garansi'                  => !empty($row['garansi']) ? trim($row['garansi']) : null,
                    'date_end'                 => !empty($row['date_end']) ? trim($row['date_end']) : null,
                    'tanggal_akhir_masa_pakai' => !empty($row['tanggal_akhir_masa_pakai']) ? trim($row['tanggal_akhir_masa_pakai']) : null,
                    'penanggung_jawab_id'      => $pjId,
                    'lokasi'                   => !empty($row['lokasi']) ? trim($row['lokasi']) : null,
                ]
            );
        }

        $this->command->info("Seeder DaftarAsetTiSeeder berhasil dijalankan (" . count($items) . " data aset).");
    }
}
