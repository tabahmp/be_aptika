<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use DOMElement;
use Exception;
use ZipArchive;

class SmkiDocxService
{
    /**
     * Cari path template dokumen FR-017.
     */
    public static function getTemplatePath(): string
    {
        $possiblePaths = [
            resource_path('templates/FR-017 Daftar Software Standar.docx'),
            base_path('resources/templates/FR-017 Daftar Software Standar.docx'),
            storage_path('app/templates/FR-017 Daftar Software Standar.docx'),
            base_path('storage/app/templates/FR-017 Daftar Software Standar.docx'),
            base_path('../FR-017 Daftar Software Standar.docx'),
            base_path('FR-017 Daftar Software Standar.docx'),
            'D:/1. KULIAH/NEW APTIKA TOOLS/FR-017 Daftar Software Standar.docx',
            'D:\\1. KULIAH\\NEW APTIKA TOOLS\\FR-017 Daftar Software Standar.docx',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new Exception("Template dokumen FR-017 Daftar Software Standar.docx tidak ditemukan di server.");
    }

    /**
     * Generate file DOCX terisi berdasarkan data software standar.
     *
     * @param array|\Illuminate\Support\Collection $items
     * @param array $options Opsi tambahan:
     *   - 'no_dokumen'    : string  No. Dokumen yang akan dimasukkan ke header tabel
     *   - 'no_revisi'     : string  No. Revisi yang akan dimasukkan ke header tabel
     *   - 'tanggal_berlaku': string Tanggal Berlaku yang akan dimasukkan ke header tabel
     * @return string Path file sementara (temporary docx file)
     */
    public function generateDocx($items, array $options = []): string
    {
        $templatePath = self::getTemplatePath();

        // Buat temporary file untuk output docx
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $outputPath = $tempDir . '/FR-017_generated_' . uniqid() . '.docx';
        if (!copy($templatePath, $outputPath)) {
            throw new Exception("Gagal menyalin file template FR-017.");
        }

        $zip = new ZipArchive();
        if ($zip->open($outputPath) !== true) {
            throw new Exception("Gagal membuka file DOCX.");
        }

        $xml = $zip->getFromName('word/document.xml');
        if (!$xml) {
            $zip->close();
            throw new Exception("Format dokumen tidak memiliki word/document.xml.");
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;
        @$dom->loadXML($xml);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $tables = $xpath->query('//w:tbl');
        if ($tables->length < 2) {
            $zip->close();
            throw new Exception("Struktur tabel dalam template FR-017 tidak valid.");
        }

        // ===================================================================
        // TABLE 0: Header dokumen — isi No Dokumen, No Revisi, Tanggal Berlaku
        // ===================================================================
        $headerTable = $tables->item(0);
        $headerRows  = $xpath->query('w:tr', $headerTable);

        // Row 0, Cell 4 => No Dokumen
        if (!empty($options['no_dokumen']) && $headerRows->length > 0) {
            $this->setCellText($xpath, $dom, $headerRows->item(0), 4, $options['no_dokumen']);
        }
        // Row 1, Cell 4 => No Revisi
        if (!empty($options['no_revisi']) && $headerRows->length > 1) {
            $this->setCellText($xpath, $dom, $headerRows->item(1), 4, $options['no_revisi']);
        }
        // Row 2, Cell 4 => Tanggal Berlaku
        if (!empty($options['tanggal_berlaku']) && $headerRows->length > 2) {
            $this->setCellText($xpath, $dom, $headerRows->item(2), 4, $options['tanggal_berlaku']);
        }

        // ===================================================================
        // TABLE 1: Data Software Standar
        // ===================================================================
        $dataTable = $tables->item(1);
        $rows = $xpath->query('w:tr', $dataTable);
        if ($rows->length < 2) {
            $zip->close();
            throw new Exception("Struktur baris tabel FR-017 tidak memiliki baris data acuan.");
        }

        // Baris 1 adalah template acuan style (simpan sebelum dihapus)
        $sampleRow = $rows->item(1);

        // Bersihkan vMerge dari sample row agar bisa jadi template yang mandiri
        $templateRow = $sampleRow->cloneNode(true);
        $vMerges = $xpath->query('.//w:vMerge', $templateRow);
        foreach ($vMerges as $vm) {
            $vm->parentNode->removeChild($vm);
        }

        // Hapus baris lama (dari baris 1 sampai akhir)
        for ($i = $rows->length - 1; $i >= 1; $i--) {
            $dataTable->removeChild($rows->item($i));
        }

        // ===================================================================
        // Kelompokkan item berdasarkan nomor_kelompok jika ada
        // ===================================================================
        $groups = $this->groupItems($items);

        foreach ($groups as $nomorKelompok => $groupItems) {
            $groupCount = count($groupItems);

            foreach ($groupItems as $subIdx => $item) {
                $isFirstInGroup = ($subIdx === 0);

                // Ekstrak nilai field
                $namaSoftware = $this->getField($item, 'nama_software');
                $versi        = $this->getField($item, 'versi');
                $tipe         = $this->getNestedField($item, 'tipe_software', 'nama_tipe_software', 'tipeSoftware');
                $penyedia     = $this->getNestedField($item, 'penyedia_barang', 'nama_penyedia_barang', 'penyediaBarang');
                $kategori     = $this->getNestedField($item, 'kategori', 'nama_kategori', 'kategori');

                // Kolom No: tampilkan hanya di baris pertama tiap kelompok
                $nomorText = $isFirstInGroup ? (string) $nomorKelompok . '.' : '';

                $cellData = [
                    $nomorText,
                    (string) ($namaSoftware ?: '-'),
                    (string) ($tipe ?: '-'),
                    (string) ($versi ?: '-'),
                    (string) ($penyedia ?: '-'),
                    (string) ($kategori ?: '-'),
                ];

                $newRow = $templateRow->cloneNode(true);
                $cells  = $xpath->query('w:tc', $newRow);

                for ($cIdx = 0; $cIdx < count($cellData) && $cIdx < $cells->length; $cIdx++) {
                    $cell = $cells->item($cIdx);
                    $value = $cellData[$cIdx];

                    // Untuk kolom No (cIdx=0): tambahkan vMerge jika group > 1 baris
                    if ($cIdx === 0 && $groupCount > 1) {
                        $this->applyVMerge($dom, $xpath, $cell, $isFirstInGroup);
                    }

                    // Kolom Penyedia Barang/Jasa (cIdx=4): font Arial ukuran 9
                    $fontOptions = ($cIdx === 4)
                        ? ['font' => 'Arial', 'size' => 9]
                        : [];

                    $this->writeCellText($dom, $xpath, $cell, $value, $fontOptions);
                }

                $dataTable->appendChild($newRow);
            }
        }

        // Jika data kosong, masukkan 1 baris placeholder agar dokumen tetap rapi dan valid
        if (empty($groups)) {
            $newRow = $templateRow->cloneNode(true);
            $cells  = $xpath->query('w:tc', $newRow);
            $placeholderData = ['1.', '-', '-', '-', '-', '-'];
            for ($cIdx = 0; $cIdx < count($placeholderData) && $cIdx < $cells->length; $cIdx++) {
                $this->writeCellText($dom, $xpath, $cells->item($cIdx), $placeholderData[$cIdx]);
            }
            $dataTable->appendChild($newRow);
        }

        // Simpan kembali xml ke dalam zip
        $zip->addFromString('word/document.xml', $dom->saveXML());
        $zip->close();

        return $outputPath;
    }

    // ========================================================================
    // PRIVATE HELPERS
    // ========================================================================

    /**
     * Kelompokkan items berdasarkan nomor_kelompok.
     * Jika nomor_kelompok tidak di-set, setiap item mendapat nomor urut sendiri.
     *
     * Return: array[ nomor => [item, ...] ]  (ordered)
     */
    private function groupItems($items): array
    {
        $groups = [];
        $autoNo = 1;

        foreach ($items as $item) {
            $kelompok = is_array($item)
                ? ($item['nomor_kelompok'] ?? null)
                : ($item->nomor_kelompok ?? null);

            if ($kelompok === null || $kelompok === '' || $kelompok === 0) {
                // Beri nomor otomatis
                $groups[$autoNo][] = $item;
                $autoNo++;
            } else {
                if (!isset($groups[(int)$kelompok])) {
                    $groups[(int)$kelompok] = [];
                }
                $groups[(int)$kelompok][] = $item;
            }
        }

        // Urutkan berdasarkan nomor kelompok
        ksort($groups);
        return $groups;
    }

    /**
     * Ambil field sederhana dari item (array atau object).
     */
    private function getField($item, string $field): string
    {
        $val = is_array($item) ? ($item[$field] ?? '') : ($item->$field ?? '');
        return (string) $val;
    }

    /**
     * Ambil field nested (relasi) dari item (array atau object).
     */
    private function getNestedField($item, string $arrayKey, string $subKey, string $relationName): string
    {
        if (is_array($item)) {
            $rel = $item[$arrayKey] ?? null;
            if (is_array($rel)) {
                return (string) ($rel[$subKey] ?? '-');
            }
            return (string) ($rel ?: '-');
        } else {
            $rel = $item->$relationName ?? null;
            return (string) ($rel->$subKey ?? '-');
        }
    }

    /**
     * Set teks pada cell ke-N dalam sebuah row.
     */
    private function setCellText(DOMXPath $xpath, DOMDocument $dom, $row, int $cellIndex, string $text): void
    {
        $cells = $xpath->query('w:tc', $row);
        if ($cells->length <= $cellIndex) {
            return;
        }
        $this->writeCellText($dom, $xpath, $cells->item($cellIndex), $text);
    }

    /**
     * Tulis teks ke dalam cell DOCX (ganti semua w:t, hapus yg berlebih).
     *
     * @param array $fontOptions Opsional: ['font' => 'Arial', 'size' => 9]
     *   'font' : nama font (string), e.g. 'Arial'
     *   'size' : ukuran font dalam pt (integer), e.g. 9
     */
    private function writeCellText(DOMDocument $dom, DOMXPath $xpath, $cell, string $text, array $fontOptions = []): void
    {
        $textNodes = $xpath->query('.//w:t', $cell);

        if ($textNodes->length > 0) {
            // Gunakan createTextNode agar karakter spesial XML (&, <, >, dll)
            // di-escape otomatis (mencegah error "unterminated entity reference")
            $wt = $textNodes->item(0);
            // Hapus semua child text node lama
            while ($wt->firstChild) {
                $wt->removeChild($wt->firstChild);
            }
            $wt->appendChild($dom->createTextNode($text));

            // Hapus node w:t berlebih di cell tersebut
            for ($k = 1; $k < $textNodes->length; $k++) {
                $tn = $textNodes->item($k);
                $tn->parentNode->removeChild($tn);
            }
            // Terapkan font options pada run parent dari w:t pertama
            if (!empty($fontOptions)) {
                $runNode = $textNodes->item(0)->parentNode;
                if ($runNode) {
                    $this->applyRunFont($dom, $xpath, $runNode, $fontOptions);
                }
            }
        } else {
            // Tidak ada w:t, buat baru
            $p = $xpath->query('.//w:p', $cell)->item(0);
            if ($p) {
                $r = $dom->createElement('w:r');
                // Sisipkan rPr sebelum w:t jika ada font options
                if (!empty($fontOptions)) {
                    $this->applyRunFont($dom, $xpath, $r, $fontOptions);
                }
                $t = $dom->createElement('w:t');
                $t->appendChild($dom->createTextNode($text));
                $r->appendChild($t);
                $p->appendChild($r);
            }
        }
    }

    /**
     * Tambahkan atau modifikasi elemen w:vMerge pada kolom 0 cell
     * untuk menggabungkan baris secara vertikal di DOCX.
     *
     * - Baris pertama kelompok: w:vMerge w:val="restart"
     * - Baris lanjutan: w:vMerge (tanpa atribut val = continue)
     */
    private function applyVMerge(DOMDocument $dom, DOMXPath $xpath, $cell, bool $isFirst): void
    {
        // Hapus vMerge lama jika ada
        $existingVMerge = $xpath->query('.//w:vMerge', $cell);
        foreach ($existingVMerge as $vm) {
            $vm->parentNode->removeChild($vm);
        }

        // Ambil elemen tcPr (cell properties)
        $tcPr = $xpath->query('w:tcPr', $cell)->item(0);
        if (!$tcPr) {
            $tcPr = $dom->createElement('w:tcPr');
            $cell->insertBefore($tcPr, $cell->firstChild);
        }

        $vMerge = $dom->createElement('w:vMerge');
        if ($isFirst) {
            $vMerge->setAttribute('w:val', 'restart');
        }
        // Tambahkan setelah elemen lain di tcPr
        $tcPr->appendChild($vMerge);
    }

    /**
     * Terapkan font dan ukuran pada elemen run (w:r).
     *
     * Akan menambahkan/memperbarui elemen w:rPr di dalam $runNode dengan:
     *   - w:rFonts  : font family (ascii, hAnsi, cs)
     *   - w:sz      : ukuran font dalam half-points (pt * 2)
     *   - w:szCs    : ukuran font untuk complex script
     *
     * @param DOMDocument $dom
     * @param DOMXPath    $xpath
     * @param \DOMNode    $runNode  Elemen w:r target
     * @param array       $fontOptions ['font' => 'Arial', 'size' => 9]
     */
    private function applyRunFont(DOMDocument $dom, DOMXPath $xpath, $runNode, array $fontOptions): void
    {
        $fontName = $fontOptions['font'] ?? null;
        $fontSize  = isset($fontOptions['size']) ? (int) $fontOptions['size'] : null;

        if (!$fontName && !$fontSize) {
            return;
        }

        // Ambil atau buat elemen w:rPr (harus jadi child pertama w:r)
        $rPrList = $xpath->query('w:rPr', $runNode);
        if ($rPrList->length > 0) {
            $rPr = $rPrList->item(0);
        } else {
            $rPr = $dom->createElement('w:rPr');
            $runNode->insertBefore($rPr, $runNode->firstChild);
        }

        // Set font family
        if ($fontName) {
            // Hapus w:rFonts lama jika ada
            $oldFonts = $xpath->query('w:rFonts', $rPr);
            foreach ($oldFonts as $of) {
                $rPr->removeChild($of);
            }

            $rFonts = $dom->createElement('w:rFonts');
            $rFonts->setAttribute('w:ascii',   $fontName);
            $rFonts->setAttribute('w:hAnsi',   $fontName);
            $rFonts->setAttribute('w:cs',      $fontName);
            $rPr->insertBefore($rFonts, $rPr->firstChild);
        }

        // Set ukuran font (dalam half-points: pt * 2)
        if ($fontSize) {
            $halfPoints = (string) ($fontSize * 2);

            // w:sz
            $oldSz = $xpath->query('w:sz', $rPr);
            foreach ($oldSz as $os) { $rPr->removeChild($os); }
            $szEl = $dom->createElement('w:sz');
            $szEl->setAttribute('w:val', $halfPoints);
            $rPr->appendChild($szEl);

            // w:szCs (complex script)
            $oldSzCs = $xpath->query('w:szCs', $rPr);
            foreach ($oldSzCs as $os) { $rPr->removeChild($os); }
            $szCsEl = $dom->createElement('w:szCs');
            $szCsEl->setAttribute('w:val', $halfPoints);
            $rPr->appendChild($szCsEl);
        }
    }
}

