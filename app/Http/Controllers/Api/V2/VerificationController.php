<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function verify(string $code): View
    {
        $document = Document::with('template.category')
            ->where('verification_code', strtoupper($code))
            ->first();

        return view('verify.show', compact('document'));
    }
}
