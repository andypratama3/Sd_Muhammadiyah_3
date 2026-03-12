<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentTemplate;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentGeneratorService
{
    /*
    |--------------------------------------------------------------------------
    | KONSTANTA UKURAN HALAMAN A4 (dalam point)
    |--------------------------------------------------------------------------
    | A4: 595.28pt × 841.89pt – ini adalah nilai PASTI yang harus dipakai
    | di semua CSS. Jangan mengubah nilai ini.
    |--------------------------------------------------------------------------
    */

    private const A4_W = 595.28;
    private const A4_H = 841.89;


    /*
    |--------------------------------------------------------------------------
    | BUILD HTML
    |--------------------------------------------------------------------------
    | Proses:
    | 1. Normalisasi HTML legacy (right/bottom → left/top, ukuran lama → baku)
    | 2. Replace semua variabel {{key}} dengan nilai data
    | 3. Replace {{logo}} dengan <img> base64
    | 4. Replace {{barcode_signature}} dengan <img> QR base64 PNG
    | 5. Hapus placeholder yang belum ter-replace
    |
    | PENTING: Struktur HTML template dari Fabric.js tidak diubah sama sekali.
    | Semua absolute positioning dalam pt tetap dipertahankan.
    |--------------------------------------------------------------------------
    */

    public function buildHtml(DocumentTemplate $template, array $data, string $verificationCode): string
    {
        $html = $template->html_template;

        // ── 1. Normalisasi legacy HTML ──────────────────────────────────────
        $html = $this->normalizeHtml($html);

        // ── 2. Replace variabel data ────────────────────────────────────────
        //    htmlspecialchars($value) dipakai agar karakter seperti & < > tidak
        //    merusak struktur HTML di dalam DomPDF.
        foreach ($data as $key => $value) {
            // Jangan gunakan htmlspecialchars — nilai mungkin terdapat HTML
            // (misalnya <b>, <br>) yang valid dari form / import.
            // Template HTML dari Fabric.js sudah ter-struktur;
            // raw string replacement cukup aman di sini.
            $html = str_replace('{{' . $key . '}}', (string) ($value ?? ''), $html);
        }

        // ── 3. Replace logo ─────────────────────────────────────────────────
        //    Selalu embed sebagai base64 agar DomPDF tidak gagal load URL.
        //    Gunakan object-fit:contain agar gambar tidak terdistorsi di dalam
        //    container absolute-positioned dari template editor.
        if ($template->hasLogo()) {
            $logoBase64 = $template->category->getLogoBase64();

            $logoHtml = $logoBase64
                ? '<img src="' . $logoBase64 . '" style="display:block;width:100%;height:100%;object-fit:contain;">'
                : '';

            $html = str_replace('{{logo}}', $logoHtml, $html);
        }

        // ── 4. Replace QR Code ──────────────────────────────────────────────
        //    QR di-render sebagai base64 PNG (bukan SVG inline) karena DomPDF
        //    memiliki bug rendering SVG inline yang menyebabkan shift posisi.
        if ($template->hasBarcodeSignature()) {
            $verifyUrl = url('/verify/' . $verificationCode);
            $html = str_replace('{{barcode_signature}}', $this->generateQrHtml($verifyUrl), $html);
        }

        // ── 5. Hapus placeholder yang tidak memiliki data ───────────────────
        //    Regex ini aman dan tidak menyentuh struktur HTML template.
        $html = preg_replace('/\{\{\s*[\w]+\s*\}\}/', '', $html);

        return $html;
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE QR CODE (BASE64 PNG)
    |--------------------------------------------------------------------------
    | QR dihasilkan sebagai PNG base64, bukan SVG inline, karena:
    | - SVG inline di DomPDF kadang menyebabkan posisi bergeser
    | - PNG base64 jauh lebih stabil dan predictable di DomPDF
    |
    | Fallback: jika Imagick tidak tersedia, gunakan SVG base64.
    | Kedua cara di-embed sebagai <img src="data:..."> sehingga DomPDF
    | tidak perlu fetch resource eksternal.
    |
    | Size QR: 90pt (sesuai ukuran container {{barcode_signature}} di editor).
    | Pastikan container di template editor dibuat 90pt × 90pt.
    |--------------------------------------------------------------------------
    */

    public function generateQrHtml(string $content): string
    {
        // ── Coba gunakan PNG via Imagick ────────────────────────────────────
        if (extension_loaded('imagick')) {
            try {
                $renderer = new ImageRenderer(
                    new RendererStyle(300),              // render 200px, di-scale oleh CSS
                    new ImagickImageBackEnd()
                );

                $writer  = new Writer($renderer);
                $png     = $writer->writeString($content);
                $base64  = 'data:image/png;base64,' . base64_encode($png);

                return '<img src="' . $base64 . '" '
                    . 'style="display:block;width:90pt;height:90pt;'
                    . 'image-rendering:pixelated;">';
            } catch (\Throwable $e) {
                // fallback ke SVG
            }
        }

        // ── Fallback: SVG base64 ────────────────────────────────────────────
        //    Di-embed sebagai <img src="data:image/svg+xml;base64,...">
        //    bukan SVG inline — ini penting agar DomPDF tidak salah parse
        //    dan tidak memindahkan posisi elemen.
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg    = $writer->writeString($content);
        $base64 = 'data:image/svg+xml;base64,' . base64_encode($svg);

        return '<img src="' . $base64 . '" '
            . 'style="display:block;width:90pt;height:90pt;">';
    }


    /*
    |--------------------------------------------------------------------------
    | HTML → PDF
    |--------------------------------------------------------------------------
    | Option DomPDF yang digunakan:
    | - isRemoteEnabled: false  → matikan remote URL, semua asset harus base64
    | - isHtml5ParserEnabled: true → parsing HTML modern lebih akurat
    | - dpi: 96  → konsisten dengan browser default
    | - defaultFont: DejaVu Sans → font yang paling stabil di DomPDF
    | - isFontSubsettingEnabled: true → kurangi ukuran PDF
    | - isJavascriptEnabled: false → matikan JS, tidak dibutuhkan
    |
    | isRemoteEnabled sengaja diset FALSE karena:
    | - Semua image sudah base64 dari buildHtml()
    | - Remote enabled justru menyebabkan timeout dan layout shift
    |--------------------------------------------------------------------------
    */

    public function htmlToPdf(string $html): string
    {
        $wrappedHtml = $this->wrapWithDocumentStyles($html);

        $pdf = Pdf::loadHTML($wrappedHtml)
            ->setPaper([0, 0, self::A4_W, self::A4_H], 'portrait')
            ->setOptions([
                'defaultFont'            => 'DejaVu Sans',
                'isRemoteEnabled'        => false,
                'isHtml5ParserEnabled'   => true,
                'dpi'                    => 96,
                'isFontSubsettingEnabled'=> true,
                'isJavascriptEnabled'    => false,
                'chroot'                 => public_path(),
            ]);

        return $pdf->output();
    }


    /*
    |--------------------------------------------------------------------------
    | GLOBAL PDF STYLE WRAPPER
    |--------------------------------------------------------------------------
    | CSS ini dirancang khusus untuk memastikan DomPDF merender template
    | Fabric.js dengan pixel-perfect. Prinsip-prinsip utama:
    |
    | 1. @page margin 0 → hapus margin bawaan DomPDF
    | 2. html/body margin 0, ukuran PERSIS A4 dalam pt
    | 3. .page menggunakan position:relative + overflow:hidden
    |    → root positioning context untuk semua child absolute
    | 4. box-sizing:border-box pada semua elemen → kalkulasi ukuran konsisten
    | 5. word-break:break-word → teks tidak overflow keluar container
    | 6. img display:block + max-width/height:100% → tidak merusak layout
    | 7. svg display:block → tidak ada extra whitespace dari SVG
    | 8. table border-collapse + tr page-break-inside:avoid
    | 9. * { margin:0; padding:0 } — hindari browser default margin
    |--------------------------------------------------------------------------
    */

    public function wrapWithDocumentStyles(string $body): string
    {
        $w = self::A4_W;
        $h = self::A4_H;

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width={$w}pt">
<style>

@page {
    margin: 0;
    size: "{$w}"pt "{$h}"pt;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body{
-webkit-font-smoothing: antialiased;
}

html, body {
    margin: 0;
    padding: 0;
    width: "{$w}"pt;
    height: "{$h}"pt;
    font-family: "DejaVu Sans", Arial, sans-serif;
    font-size: 12pt;
    line-height: 1.3;
    background: #ffffff;
    color: #000000;
}

.page {
    position: relative;         
    width: "{$w}"pt;
    height: "{$h}"pt;
    overflow: hidden;           
    page-break-after: always;   
    page-break-inside: avoid;
    background: #ffffff;
}

.page:last-child {
    page-break-after: auto;
}

.page > div,
.page div {
    box-sizing: border-box;
    word-break: break-word;
    overflow-wrap: break-word;
    white-space: normal;        
}

p, span {
    margin: 0;
    padding: 0;
}

img {
    display: block;
    max-width: 100%;
    max-height: 100%;
    border: 0;
    outline: 0;
    image-rendering: optimizeQuality;
}

svg {
    display: block;
    overflow: hidden;
}
table {
    border-collapse: collapse;
    border-spacing: 0;
    table-layout: fixed;        
}

thead {
    display: table-header-group;
}

tr {
    page-break-inside: avoid;   
}

td, th {
    vertical-align: top;
    word-break: break-word;
    overflow-wrap: break-word;
    padding: 2pt;
    white-space: normal !important;
}
u { text-decoration: underline; }
b, strong { font-weight: bold; }
i, em { font-style: italic; }

</style>
</head>
<body>
{$body}
</body>
</html>
HTML;
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE SINGLE DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function generate(DocumentTemplate $template, array $userData, array $meta = []): Document
    {
        $verificationCode = strtoupper(Str::random(12));

        $html = $this->buildHtml($template, $userData, $verificationCode);
        $pdf  = $this->htmlToPdf($html);

        $filename = 'documents/' . $verificationCode . '.pdf';

        Storage::disk('public')->put($filename, $pdf);

        return Document::create(array_merge([
            'template_id'       => $template->id,
            'data_json'         => $userData,
            'file_path'         => $filename,
            'verification_code' => $verificationCode,
        ], $meta));
    }


    /*
    |--------------------------------------------------------------------------
    | BULK GENERATE DOCUMENTS (dokumen terpisah per-baris)
    |--------------------------------------------------------------------------
    | Dokumen disimpan terpisah, setiap baris data menghasilkan 1 file PDF.
    | Progress callback dipanggil setiap dokumen selesai diproses.
    |--------------------------------------------------------------------------
    */

    public function generateBulk(
        DocumentTemplate $template,
        iterable $rows,
        ?callable $progressCallback = null
    ): Collection {

        $results = collect();
        $rows    = collect($rows);
        $total   = $rows->count();
        $done    = 0;

        foreach ($rows as $row) {
            $meta = [];

            if (isset($row['_student_id'])) {
                $meta['student_id'] = $row['_student_id'];
                unset($row['_student_id']);
            }

            if (isset($row['_created_by'])) {
                $meta['created_by'] = $row['_created_by'];
                unset($row['_created_by']);
            }

            $doc = $this->generate($template, $row, $meta);
            $results->push($doc);

            $done++;

            if ($progressCallback) {
                $progressCallback($done, $total, $doc);
            }
        }

        return $results;
    }


    /*
    |--------------------------------------------------------------------------
    | BULK MERGED PDF (semua halaman dalam satu file PDF)
    |--------------------------------------------------------------------------
    | Setiap baris data dirender menjadi satu halaman .page di dalam
    | satu dokumen HTML, lalu seluruhnya di-convert ke satu PDF.
    |
    | Perbaikan dari versi lama:
    | - buildHtml() tidak membungkus output dengan <div class="page"> ulang
    |   (template sudah mengandung .page dari Fabric.js editor)
    | - wrapWithDocumentStyles() hanya dipanggil sekali untuk semua halaman
    | - Filter row yang diawali underscore (_student_id, _created_by) tetap
    |   dilakukan agar tidak mengganggu variable replacement
    |--------------------------------------------------------------------------
    */

    public function generateBulkMergedPdf(
        DocumentTemplate $template,
        iterable $rows,
        string $title = 'bulk-document'
    ): string {

        $rows  = collect($rows);
        $pages = [];

        foreach ($rows as $row) {
            // Pisahkan meta keys dari data template
            $userData = collect($row)
                ->filter(fn ($v, $k) => !str_starts_with((string) $k, '_'))
                ->all();

            $verificationCode = strtoupper(Str::random(12));

            // buildHtml() menghasilkan konten DALAM .page dari template editor.
            // Template dari Fabric.js sudah menyertakan wrapper <div class="page">
            // sehingga kita tidak perlu membungkus ulang.
            $pages[] = $this->buildHtml($template, $userData, $verificationCode);
        }

        $combined = implode("\n", $pages);

        $pdf = Pdf::loadHTML($this->wrapWithDocumentStyles($combined))
            ->setPaper([0, 0, self::A4_W, self::A4_H], 'portrait')
            ->setOptions([
                'defaultFont'            => 'DejaVu Sans',
                'isRemoteEnabled'        => false,
                'isHtml5ParserEnabled'   => true,
                'dpi'                    => 96,
                'isFontSubsettingEnabled'=> true,
                'isJavascriptEnabled'    => false,
                'chroot'                 => public_path(),
            ]);

        $filename = 'documents/bulk/'
            . Str::slug($title)
            . '-'
            . now()->format('YmdHis')
            . '.pdf';

        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE LEGACY HTML
    |--------------------------------------------------------------------------
    | Fix HTML yang dihasilkan oleh versi lama generateHTML():
    |
    | Fix 1 — Ukuran container lama → ukuran A4 baku
    |   Old: 595.5pt × 842.25pt (dari 794px × 0.75 dan 1123px × 0.75)
    |   New: 595.28pt × 841.89pt
    |
    | Fix 2 — Konversi right/bottom → left/top
    |   DomPDF TIDAK mendukung right: dan bottom: pada positioned element.
    |   Rumus:
    |     left = A4_W - right - element_width
    |     top  = A4_H - bottom - element_height
    |--------------------------------------------------------------------------
    */

    private function normalizeHtml(string $html): string
    {
        $w = self::A4_W;
        $h = self::A4_H;

        // ── Fix 1: Normalisasi ukuran container lama ────────────────────────
        $html = preg_replace('/width:\s*595\.5pt/', "width:{$w}pt", $html);
        $html = preg_replace('/height:\s*842\.25pt/', "height:{$h}pt", $html);

        // Variasi lain yang mungkin muncul dari generator lama
        $html = preg_replace('/width:\s*595\.5\s*pt/', "width:{$w}pt", $html);
        $html = preg_replace('/height:\s*842\.25\s*pt/', "height:{$h}pt", $html);

        // ── Fix 2: right/bottom → left/top ─────────────────────────────────
        $html = preg_replace_callback(
            '/position:\s*absolute;\s*right:\s*([\d.]+)pt;\s*bottom:\s*([\d.]+)pt;\s*width:\s*([\d.]+)pt;\s*height:\s*([\d.]+)pt/',
            function ($m) use ($w, $h) {
                $right  = (float) $m[1];
                $bottom = (float) $m[2];
                $ew     = (float) $m[3];
                $eh     = (float) $m[4];

                $left = round($w - $right - $ew, 2);
                $top  = round($h - $bottom - $eh, 2);

                return "position:absolute;left:{$left}pt;top:{$top}pt;width:{$ew}pt;height:{$eh}pt";
            },
            $html
        );

        return $html;
    }
}