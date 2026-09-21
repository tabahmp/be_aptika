<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Exception;
use ZipArchive;

class SmkiDaftarRekamanDocxService
{
    /**
     * Path template dokumen FR-003.
     */
    public static function getTemplatePath(): string
    {
        $possiblePaths = [
            resource_path('templates/FR-003 Formulir Daftar Rekaman.docx'),
            base_path('resources/templates/FR-003 Formulir Daftar Rekaman.docx'),
            storage_path('app/templates/FR-003 Formulir Daftar Rekaman.docx'),
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new Exception("Template dokumen FR-003 Formulir Daftar Rekaman.docx tidak ditemukan di server.");
    }

    /**
     * Generate file DOCX terisi berdasarkan data rekaman SMKI.
     *
     * @param array|\Illuminate\Support\Collection $items
     * @param array $options
     * @return string Path file temporary docx
     */
    public function generateDocx($items, array $options = []): string
    {
        $templatePath = self::getTemplatePath();

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $outputPath = $tempDir . '/FR-003_generated_' . uniqid() . '.docx';
        if (!copy($templatePath, $outputPath)) {
            throw new Exception("Gagal menyalin file template FR-003.");
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
            throw new Exception("Struktur tabel dalam template FR-003 tidak valid.");
        }

        // -------------------------------------------------------------------
        // TABLE 0: Header Dokumen (No. Dokumen, No. Revisi, Tanggal Berlaku)
        // -------------------------------------------------------------------
        $headerTable = $tables->item(0);
        $headerRows  = $xpath->query('w:tr', $headerTable);

        if (!empty($options['no_dokumen']) && $headerRows->length > 0) {
            $this->setCellText($xpath, $dom, $headerRows->item(0), 4, $options['no_dokumen']);
        }
        if (!empty($options['no_revisi']) && $headerRows->length > 1) {
            $this->setCellText($xpath, $dom, $headerRows->item(1), 4, $options['no_revisi']);
        }
        if (!empty($options['tanggal_berlaku']) && $headerRows->length > 2) {
            $this->setCellText($xpath, $dom, $headerRows->item(2), 4, $options['tanggal_berlaku']);
        }

        // -------------------------------------------------------------------
        // TABLE 1: Tabel Data Rekaman
        // Row 2 adalah Header ("No", "Judul Rekaman", "Klasifikasi", "Retensi", "Pemilik")
        // Row 3 adalah Sample Data Row
        // -------------------------------------------------------------------
        $dataTable = $tables->item(1);
        $rows = $xpath->query('w:tr', $dataTable);
        if ($rows->length < 4) {
            $zip->close();
            throw new Exception("Struktur baris tabel FR-003 tidak memiliki baris data acuan.");
        }

        // Sample row pada baris ke-3 (index 3)
        $sampleRow = $rows->item(3);
        $templateRow = $sampleRow->cloneNode(true);

        // Hapus baris lama (dari baris 3 sampai akhir)
        for ($i = $rows->length - 1; $i >= 3; $i--) {
            $dataTable->removeChild($rows->item($i));
        }

        // Populate baris data
        $index = 1;
        foreach ($items as $item) {
            $judul       = $this->getField($item, 'judul');
            $klasifikasi = $this->getNestedField($item, 'klasifikasi', 'nama_klasifikasi', 'klasifikasi');
            $retensi     = $this->getNestedField($item, 'retensi', 'nama_retensi', 'retensi');
            $pemilik     = $this->getNestedField($item, 'pemilik', 'nama_pemilik', 'pemilik');

            $cellData = [
                (string) $index++,
                (string) ($judul ?: '-'),
                (string) ($klasifikasi ?: '-'),
                (string) ($retensi ?: '-'),
                (string) ($pemilik ?: '-'),
            ];

            $newRow = $templateRow->cloneNode(true);
            $cells  = $xpath->query('w:tc', $newRow);

            for ($cIdx = 0; $cIdx < count($cellData) && $cIdx < $cells->length; $cIdx++) {
                $cell = $cells->item($cIdx);
                $this->writeCellText($dom, $xpath, $cell, $cellData[$cIdx]);
            }

            $dataTable->appendChild($newRow);
        }

        // Jika data kosong, masukkan 1 baris placeholder
        if (count($items) === 0) {
            $newRow = $templateRow->cloneNode(true);
            $cells  = $xpath->query('w:tc', $newRow);
            $placeholderData = ['1', '-', '-', '-', '-'];
            for ($cIdx = 0; $cIdx < count($placeholderData) && $cIdx < $cells->length; $cIdx++) {
                $this->writeCellText($dom, $xpath, $cells->item($cIdx), $placeholderData[$cIdx]);
            }
            $dataTable->appendChild($newRow);
        }

        $zip->addFromString('word/document.xml', $dom->saveXML());
        $zip->close();

        return $outputPath;
    }

    private function getField($item, string $key)
    {
        if (is_array($item)) {
            return $item[$key] ?? null;
        }
        return $item->{$key} ?? null;
    }

    private function getNestedField($item, string $objKey, string $propKey, string $relationName)
    {
        if (is_array($item)) {
            if (isset($item[$objKey]) && is_array($item[$objKey])) {
                return $item[$objKey][$propKey] ?? null;
            }
            return null;
        }

        if (isset($item->{$relationName}) && is_object($item->{$relationName})) {
            return $item->{$relationName}->{$propKey} ?? null;
        }
        return null;
    }

    private function setCellText(DOMXPath $xpath, DOMDocument $dom, $rowNode, int $cellIndex, string $text)
    {
        $cells = $xpath->query('w:tc', $rowNode);
        if ($cells->length > $cellIndex) {
            $this->writeCellText($dom, $xpath, $cells->item($cellIndex), $text);
        }
    }

    private function writeCellText(DOMDocument $dom, DOMXPath $xpath, $cellNode, string $text)
    {
        $paragraphs = $xpath->query('w:p', $cellNode);
        if ($paragraphs->length > 0) {
            $p = $paragraphs->item(0);

            $pPr = $xpath->query('w:pPr', $p)->item(0);
            $rPr = null;

            $existingRuns = $xpath->query('w:r', $p);
            if ($existingRuns->length > 0) {
                $firstRun = $existingRuns->item(0);
                $existingRPr = $xpath->query('w:rPr', $firstRun)->item(0);
                if ($existingRPr) {
                    $rPr = $existingRPr->cloneNode(true);
                }
            }

            foreach ($existingRuns as $run) {
                $p->removeChild($run);
            }

            $newRun = $dom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:r');
            if ($rPr) {
                $newRun->appendChild($rPr);
            }

            $newText = $dom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:t');
            $newText->nodeValue = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
            if (trim($text) !== $text) {
                $newText->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
            }

            $newRun->appendChild($newText);
            $p->appendChild($newRun);
        }
    }
}
