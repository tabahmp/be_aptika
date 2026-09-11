<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class DaftarAsetTiExportService
{
    /**
     * Menghasilkan berkas Excel berdasarkan koleksi data aset TI.
     * Format 100% sama persis dengan template resmi:
     * "D:\1. KULIAH\NEW APTIKA TOOLS\Daftar Aset TI_Bidang APTIKA.xlsx"
     * Meliputi:
     * - Sheet tunggal bernama "ASET TI"
     * - 18 Kolom resmi dari Kolom B sampai S dengan ukuran lebar kolom yang sama persis
     * - Header judul dan nomor urut kolom (baris 5 & 6)
     * - Bagian tanda tangan "KEPALA BIDANG APLIKASI INFORMATIKA"
     * - Bagian "Catatan:"
     * - Bagian "Keterangan Kolom:" (Kolom 1 s.d. Kolom 18)
     */
    public function generateExcel(Collection $items, array $options = []): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ASET TI');
        $sheet->setShowGridLines(true);

        // 1. Column Widths sama persis dengan berkas acuan
        $columnWidths = [
            'A' => 4.29,
            'B' => 7.14,
            'C' => 20.86,
            'D' => 48.71,
            'E' => 20.57,
            'F' => 12.57,
            'G' => 15.57,
            'H' => 35.14,
            'I' => 31.00,
            'J' => 47.14,
            'K' => 58.14,
            'L' => 41.57,
            'M' => 14.29,
            'N' => 15.71,
            'O' => 10.86,
            'P' => 13.71,
            'Q' => 10.29,
            'R' => 30.00,
            'S' => 26.29,
        ];

        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // 2. Baris Judul (Row 2)
        $sheet->mergeCells('B2:S2');
        $sheet->setCellValue('B2', 'DAFTAR ASET TEKNOLOGI INFORMASI BIDANG APTIKA');
        $sheet->getRowDimension(2)->setRowHeight(58.5);
        $sheet->getStyle('B2')->getFont()->setName('Calibri')->setSize(22)->setBold(true);
        $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_BOTTOM);

        // Baris spasi kecil (Row 4)
        $sheet->getRowDimension(4)->setRowHeight(3);

        // 3. Header Tabel (Row 5)
        $headers = [
            'B' => 'No.',
            'C' => 'Kode',
            'D' => 'Nama Aset',
            'E' => 'Klasifikasi',
            'F' => 'Jenis',
            'G' => 'Kategori',
            'H' => 'Nomor Seri',
            'I' => 'Merek',
            'J' => 'Tipe',
            'K' => 'Spesifikasi Teknis',
            'L' => 'Pemanfaatan',
            'M' => 'Penyedia',
            'N' => 'Tahun Pembelian',
            'O' => 'Garansi',
            'P' => 'End of Support',
            'Q' => 'End of Life',
            'R' => 'Lokasi',
            'S' => 'Penanggung Jawab',
        ];

        $sheet->getRowDimension(5)->setRowHeight(36);
        foreach ($headers as $col => $title) {
            $sheet->setCellValue("{$col}5", $title);
        }

        $headerStyle = [
            'font' => ['name' => 'Calibri', 'size' => 14, 'bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']],
            ],
        ];
        $sheet->getStyle('B5:S5')->applyFromArray($headerStyle);
        $sheet->getStyle('N5')->getAlignment()->setWrapText(true);
        $sheet->getStyle('P5')->getAlignment()->setWrapText(true);
        $sheet->getStyle('Q5')->getAlignment()->setWrapText(true);

        // 4. Baris Nomor Kolom Indeks 1 s.d. 18 (Row 6)
        $sheet->getRowDimension(6)->setRowHeight(20);
        $colIdxNum = 1;
        foreach (array_keys($headers) as $col) {
            $sheet->setCellValue("{$col}6", (string)$colIdxNum);
            $colIdxNum++;
        }

        $sheet->getStyle('B6:S6')->applyFromArray([
            'font' => ['name' => 'Calibri', 'size' => 14, 'bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']],
            ],
        ]);

        // 5. Pengisian Data Baris (Mulai Row 7)
        $currentRow = 7;
        $no = 1;

        $thinBorder = [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']],
        ];

        foreach ($items as $item) {
            $kode = $item->kode ?: '';
            $nama = $item->nama_aset ?: '';
            $klasifikasi = $item->klasifikasi?->nama_klasifikasi ?: ($item->klasifikasi_aset ?: '');
            $jenis = $item->jenis?->nama_jenis ?: ($item->jenis_aset ?: '');
            $kategori = $item->kategori?->nama_kategori ?: ($item->kategori_aset ?: '');
            $noSeri = $item->no_seri ?: '';
            $merek = $item->merek?->nama_merek ?: ($item->merek_aset ?: '');
            $tipe = $item->tipe?->nama_tipe ?: ($item->tipe_aset ?: '');
            $spesifikasi = $item->spesifikasi_teknis ?: ($item->spesifikasi?->spesifikasi_teknis ?: '');
            $pemanfaatan = $item->pemanfaatan ?: ($item->pemanfaatanRel?->pemanfaatan ?: '');
            $penyedia = $item->penyedia?->nama_penyedia ?: ($item->penyedia_aset ?: '');
            $tahunBeli = $item->tahun_pembelian ? (string)$item->tahun_pembelian : '';
            $garansi = $item->garansi ?: '';
            $endSupport = $item->date_end ?: '';
            $endLife = $item->tanggal_akhir_masa_pakai ?: '';
            $lokasi = $item->lokasi ?: '';
            $pj = $item->penanggungJawab?->nama_pj ?: ($item->pj_aset ?: '');

            $sheet->setCellValue("B{$currentRow}", $no);
            $sheet->setCellValue("C{$currentRow}", $kode);
            $sheet->setCellValue("D{$currentRow}", $nama);
            $sheet->setCellValue("E{$currentRow}", $klasifikasi);
            $sheet->setCellValue("F{$currentRow}", $jenis);
            $sheet->setCellValue("G{$currentRow}", $kategori);
            $sheet->setCellValue("H{$currentRow}", $noSeri);
            $sheet->setCellValue("I{$currentRow}", $merek);
            $sheet->setCellValue("J{$currentRow}", $tipe);
            $sheet->setCellValue("K{$currentRow}", $spesifikasi);
            $sheet->setCellValue("L{$currentRow}", $pemanfaatan);
            $sheet->setCellValue("M{$currentRow}", $penyedia);
            $sheet->setCellValue("N{$currentRow}", $tahunBeli);
            $sheet->setCellValue("O{$currentRow}", $garansi);
            $sheet->setCellValue("P{$currentRow}", $endSupport);
            $sheet->setCellValue("Q{$currentRow}", $endLife);
            $sheet->setCellValue("R{$currentRow}", $lokasi);
            $sheet->setCellValue("S{$currentRow}", $pj);

            // Terapkan border
            $sheet->getStyle("B{$currentRow}:S{$currentRow}")->getBorders()->applyFromArray($thinBorder);

            // Font dan Alignments sesuai berkas acuan
            $sheet->getStyle("B{$currentRow}")->getFont()->setName('Calibri')->setSize(14)->setBold(true);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_BOTTOM);

            $sheet->getStyle("C{$currentRow}")->getFont()->setName('Calibri')->setSize(11);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_BOTTOM);

            $sheet->getStyle("D{$currentRow}:J{$currentRow}")->getFont()->setName('Calibri')->setSize(12);
            $sheet->getStyle("D{$currentRow}:J{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM);

            $sheet->getStyle("K{$currentRow}")->getFont()->setName('Calibri')->setSize(12);
            $sheet->getStyle("K{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM)->setWrapText(true);

            $sheet->getStyle("L{$currentRow}")->getFont()->setName('Calibri')->setSize(12);
            $sheet->getStyle("L{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM)->setWrapText(true);

            $sheet->getStyle("M{$currentRow}")->getFont()->setName('Calibri')->setSize(12);
            $sheet->getStyle("M{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM);

            $sheet->getStyle("N{$currentRow}")->getFont()->setName('Calibri')->setSize(12);
            $sheet->getStyle("N{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_BOTTOM);

            $sheet->getStyle("O{$currentRow}:Q{$currentRow}")->getFont()->setName('Calibri')->setSize(12);
            $sheet->getStyle("O{$currentRow}:Q{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM);

            $sheet->getStyle("R{$currentRow}")->getFont()->setName('Calibri')->setSize(12);
            $sheet->getStyle("R{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_BOTTOM)->setWrapText(true);

            $sheet->getStyle("S{$currentRow}")->getFont()->setName('Calibri')->setSize(12);
            $sheet->getStyle("S{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_BOTTOM)->setWrapText(true);

            $currentRow++;
            $no++;
        }

        // 6. Bagian Tanda Tangan: KEPALA BIDANG APLIKASI INFORMATIKA
        // Berada 4 baris setelah baris data terakhir
        $sigRow = $currentRow + 3;
        $sheet->setCellValue("O{$sigRow}", 'KEPALA BIDANG APLIKASI INFORMATIKA');
        $sheet->getStyle("O{$sigRow}")->getFont()->setName('Calibri')->setSize(14)->setBold(true);
        $sheet->getRowDimension($sigRow)->setRowHeight(15.75);

        // Tambahkan Gambar Tanda Tangan Elektronik (TTE) tepat di bawah KEPALA BIDANG APLIKASI INFORMATIKA
        $ttePath = self::getTteImagePath();
        if ($ttePath && file_exists($ttePath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Tanda Tangan Elektronik');
            $drawing->setDescription('Ditandatangani secara elektronik oleh KEPALA BIDANG APLIKASI INFORMATIKA');
            $drawing->setPath($ttePath);
            $drawing->setCoordinates("N" . ($sigRow + 1));
            $drawing->setOffsetX(109);
            $drawing->setOffsetY(7);
            $drawing->setWidth(375);
            $drawing->setHeight(133);
            $drawing->setWorksheet($sheet);
        }

        // Merge cell untuk ruang tanda tangan (P67:Q67 pada acuan asli)
        $mergeSigRow = $sigRow + 8;
        $sheet->mergeCells("P{$mergeSigRow}:Q{$mergeSigRow}");

        // 7. Bagian Catatan (10 baris setelah baris tanda tangan)
        $noteRow = $sigRow + 10;
        $sheet->setCellValue("B{$noteRow}", 'Catatan:');
        $sheet->getStyle("B{$noteRow}")->getFont()->setName('Calibri')->setSize(12)->setBold(true);
        $sheet->getRowDimension($noteRow)->setRowHeight(15.75);

        $noteTextRow = $noteRow + 1;
        $sheet->setCellValue("B{$noteTextRow}", '- Pengisian data merupakan semua Aset TI Milik Pemprov Jabar yang masih digunakan, termasuk aset pendukung pada data center.');
        $sheet->getStyle("B{$noteTextRow}")->getFont()->setName('Calibri')->setSize(11)->setBold(false);
        $sheet->getRowDimension($noteTextRow)->setRowHeight(15.75);

        // 8. Bagian Keterangan Kolom (Kolom 1 s.d. Kolom 18)
        $ketHeaderRow = $noteRow + 3;
        $sheet->setCellValue("B{$ketHeaderRow}", 'Keterangan Kolom:');
        $sheet->getStyle("B{$ketHeaderRow}")->getFont()->setName('Calibri')->setSize(12)->setBold(true);
        $sheet->getRowDimension($ketHeaderRow)->setRowHeight(15.75);

        $keteranganList = [
            1 => 'Nomor Urut.',
            2 => 'Kode Aset, didapatkan dari Sekretariat.',
            3 => 'Nama Perangkat.',
            4 => 'Klasifikasi Informasi terdiri dari: Publik, Terbatas, Rahasia.',
            5 => 'Hardware, Software.',
            6 => 'Kategori Perangkat (Cth. Router, Firewall, Laptop, Server, NAS, SAN, Hub, Switch, Modem, Lisensi, UPS, PAC, Fingerprint, CCTV, Access Point, Komputer dll.).',
            7 => 'Serial Number Perangkat.',
            8 => 'Nama Merk.',
            9 => 'Tipe dari Merk.',
            10 => 'Spesifikasi Teknis dari perangkat.',
            11 => 'Perangkat digunakan untuk.',
            12 => 'Nama Penyedia Perangkat.',
            13 => 'Tahun Pembelian Perangkat.',
            14 => 'Garansi sampai dengan tahun.',
            15 => 'Tahun Perangkat sudah tidak ada layanan perbaikan dari Penyedia dll, tapi masih dipergunakan.',
            16 => 'Tahun Perangkat sudah tidak dikembangkan atau tidak dibuat lagi oleh Produsen/Supplier, tapi masih dipergunakan.',
            17 => 'Tempat / Lokasi Perangkat.',
            18 => 'Unit Kerja Pemilik Perangkat / Penanggung Jawab Perangkat.',
        ];

        foreach ($keteranganList as $colNum => $desc) {
            $currKetRow = $ketHeaderRow + $colNum;
            $sheet->setCellValue("B{$currKetRow}", "Kolom {$colNum}");
            $sheet->setCellValue("D{$currKetRow}", ": {$desc}");
            $sheet->getStyle("B{$currKetRow}")->getFont()->setName('Calibri')->setSize(11);
            $sheet->getStyle("D{$currKetRow}")->getFont()->setName('Calibri')->setSize(11);
            $sheet->getRowDimension($currKetRow)->setRowHeight(15.75);
        }

        // Tulis ke berkas sementara
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $filename = 'Daftar_Aset_TI_Bidang_APTIKA_' . date('Ymd_His') . '.xlsx';
        $filePath = $tempDir . '/' . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * Dapatkan path gambar tanda tangan elektronik (TTE) Kabid Aptika.
     */
    public static function getTteImagePath(): ?string
    {
        $possiblePaths = [
            storage_path('app/templates/tte_kabid_aptika.png'),
            resource_path('templates/tte_kabid_aptika.png'),
            base_path('storage/app/templates/tte_kabid_aptika.png'),
            base_path('resources/templates/tte_kabid_aptika.png'),
            'D:/1. KULIAH/NEW APTIKA TOOLS/be_aptika/storage/app/templates/tte_kabid_aptika.png',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Fallback jika belum tersalin: ekstrak langsung dari berkas template excel
        $refFile = 'D:/1. KULIAH/NEW APTIKA TOOLS/Daftar Aset TI_Bidang APTIKA.xlsx';
        if (file_exists($refFile)) {
            $zip = new \ZipArchive();
            if ($zip->open($refFile) === true) {
                $imgData = $zip->getFromName('xl/media/image1.png');
                $zip->close();
                if ($imgData) {
                    $savePath = storage_path('app/templates/tte_kabid_aptika.png');
                    if (!is_dir(dirname($savePath))) {
                        mkdir(dirname($savePath), 0777, true);
                    }
                    file_put_contents($savePath, $imgData);
                    return $savePath;
                }
            }
        }

        return null;
    }
}

