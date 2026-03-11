<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentTemplate;
use BaconQrCode\Renderer\Image\Png;
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
    | BUILD HTML
    |--------------------------------------------------------------------------
    */

    public function buildHtml(DocumentTemplate $template, array $data, string $verificationCode): string
    {
        $html = $template->html_template;

        /*
        | Normalize legacy HTML from old generateHTML():
        | 1. Fix barcode using right:X;bottom:X → convert to left/top (DomPDF can't do bottom/right)
        | 2. Fix wrong container size 595.5pt → 595.28pt (A4 standard)
        */
        $html = $this->normalizeHtml($html);

        /*
        | Replace variables
        */
        foreach ($data as $key => $value) {
            $html = str_replace('{{'.$key.'}}', $value ?? '', $html);
        }

        /*
        | Replace logo
        */
        if ($template->hasLogo()) {

            $logoBase64 = $template->category->getLogoBase64();

            $logoHtml = $logoBase64
                ? '<img src="'.$logoBase64.'" style="width:100%;height:100%;object-fit:contain;">'
                : '';

            $html = str_replace('{{logo}}', $logoHtml, $html);
        }

        /*
        | Replace QR Code
        */
        if ($template->hasBarcodeSignature()) {

            $verifyUrl = url('/verify/'.$verificationCode);

            $html = str_replace(
                '{{barcode_signature}}',
                $this->generateQrHtml($verifyUrl),
                $html
            );
        }

        /*
        | Remove remaining placeholders
        */
        $html = preg_replace('/{{\s*[\w]+\s*}}/', '', $html);

        return $html;
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE QR CODE
    |--------------------------------------------------------------------------
    */

   public function generateQrHtml(string $content): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(120),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        $svg = $writer->writeString($content);

        $base64 = base64_encode($svg);

        return '<img src="data:image/svg+xml;base64,'.$base64.'" style="width:90pt;height:90pt;">';
    }


    /*
    |--------------------------------------------------------------------------
    | HTML → PDF
    |--------------------------------------------------------------------------
    */

   public function htmlToPdf(string $html): string
    {
        $pdf = Pdf::loadHTML(
            $this->wrapWithDocumentStyles($html)
        )
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'dpi' => 96,
            'isFontSubsettingEnabled' => true,
        ]);

        return $pdf->output();
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE SINGLE DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function generate(DocumentTemplate $template, array $userData, array $meta = []): Document
    {
        $verificationCode = strtoupper(Str::random(12));

        $html = $this->buildHtml(
            $template,
            $userData,
            $verificationCode
        );

        $pdf = $this->htmlToPdf($html);

        $filename = 'documents/'.$verificationCode.'.pdf';

        Storage::disk('public')->put($filename, $pdf);

        return Document::create(array_merge([
            'template_id' => $template->id,
            'data_json' => $userData,
            'file_path' => $filename,
            'verification_code' => $verificationCode,
        ], $meta));
    }


    /*
    |--------------------------------------------------------------------------
    | BULK GENERATE DOCUMENTS
    |--------------------------------------------------------------------------
    */

    public function generateBulk(
        DocumentTemplate $template,
        iterable $rows,
        ?callable $progressCallback = null
    ): Collection {

        $results = collect();

        $rows = collect($rows);
        $total = $rows->count();
        $done = 0;

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
    | BULK MERGED PDF
    |--------------------------------------------------------------------------
    */

    public function generateBulkMergedPdf(
        DocumentTemplate $template,
        iterable $rows,
        string $title = 'bulk-document'
    ): string {

        $rows = collect($rows);

        $pages = [];

        foreach ($rows as $row) {

            $userData = collect($row)
                ->filter(fn ($v, $k) => !str_starts_with($k, '_'))
                ->all();

            $verificationCode = strtoupper(Str::random(12));

            $pages[] =
                '<div class="page">' .
                $this->buildHtml(
                    $template,
                    $userData,
                    $verificationCode
                ) .
                '</div>';
        }

        $combined = implode('', $pages);

        $pdf = Pdf::loadHTML(
            $this->wrapWithDocumentStyles($combined)
        )
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'dpi' => 96,
            'isPhpEnabled' => true,
            'isFontSubsettingEnabled' => true,
        ]);

        $filename =
            'documents/bulk/'.
            Str::slug($title).
            '-'.
            now()->format('YmdHis').
            '.pdf';

        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }


    /*
    |--------------------------------------------------------------------------
    | GLOBAL PDF STYLE WRAPPER
    |--------------------------------------------------------------------------
    */

    private function wrapWithDocumentStyles(string $body): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<style>
html,body{
margin:0;
padding:0;
font-family:'DejaVu Sans', sans-serif;
font-size:12px;
}

.page{
position:relative;
width:595.28pt;
height:841.89pt;
overflow:hidden;
page-break-after:always;
}

img{
display:block;
max-width:100%;
}

div{
box-sizing:border-box;
white-space:pre-wrap;
word-wrap:break-word;
}

</style>

</head>

<body>

$body

</body>
</html>
HTML;

    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE LEGACY HTML
    |--------------------------------------------------------------------------
    | Fix HTML generated by older version of generateHTML():
    | 1. Convert right/bottom positioning → left/top (DomPDF compat)
    | 2. Correct container dimensions from old 0.75 ratio to A4 standard
    |--------------------------------------------------------------------------
    */

    private function normalizeHtml(string $html): string
    {
        $A4_W = 595.28;
        $A4_H = 841.89;

        /*
        | Fix 1: Normalize container size
        | Old ratio: 794*0.75 = 595.5pt, 1123*0.75 = 842.25pt
        | Correct:   595.28pt × 841.89pt
        */
        $html = preg_replace(
            '/width:\s*595\.5pt/',
            'width:' . $A4_W . 'pt',
            $html
        );
        $html = preg_replace(
            '/height:\s*842\.25pt/',
            'height:' . $A4_H . 'pt',
            $html
        );

        /*
        | Fix 2: Convert right:X;bottom:X → left/top for barcode elements
        | Old: position:absolute;right:40pt;bottom:40pt;width:Wpt;height:Hpt
        | New: position:absolute;left:(A4_W-40-W)pt;top:(A4_H-40-H)pt;width:Wpt;height:Hpt
        */
        $html = preg_replace_callback(
            '/position:\s*absolute;\s*right:\s*([\d.]+)pt;\s*bottom:\s*([\d.]+)pt;\s*width:\s*([\d.]+)pt;\s*height:\s*([\d.]+)pt/',
            function ($m) use ($A4_W, $A4_H) {
                $right  = (float) $m[1];
                $bottom = (float) $m[2];
                $w      = (float) $m[3];
                $h      = (float) $m[4];

                $left = round($A4_W - $right - $w, 2);
                $top  = round($A4_H - $bottom - $h, 2);

                return 'position:absolute;left:' . $left . 'pt;top:' . $top . 'pt;width:' . $w . 'pt;height:' . $h . 'pt';
            },
            $html
        );

        return $html;
    }
}