<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class SignatureController extends Controller
{
    public function generateSignature(): string
    {
        /*
        |--------------------------------------------------------------------------
        | Path tanda tangan
        |--------------------------------------------------------------------------
        */

        // $signaturePath = public_path('asset/img/ttd_pak_ansar.png');
        $signaturePath = "Ini Ad";

        if (!file_exists($signaturePath)) {
            abort(404,'Signature not found');
        }

        /*
        |--------------------------------------------------------------------------
        | Convert gambar → hash
        |--------------------------------------------------------------------------
        */

        $signatureHash = hash_file('sha256', $signaturePath);

        /*
        |--------------------------------------------------------------------------
        | Generate QR
        |--------------------------------------------------------------------------
        */

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        $svg = $writer->writeString($signatureHash);

        /*
        |--------------------------------------------------------------------------
        | Return HTML img
        |--------------------------------------------------------------------------
        */

        return '<img src="data:image/svg+xml;base64,'.base64_encode($svg).'"
                style="width:90pt;height:90pt;">';
    }
}