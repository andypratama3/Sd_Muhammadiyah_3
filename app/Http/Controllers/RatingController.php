<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\.\'\-]+$/'],
            'rating' => ['required', 'integer', 'between:1,5'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama tidak boleh lebih dari 100 karakter.',
            'nama.regex' => 'Nama hanya boleh mengandung huruf, spasi, titik, apostrof, atau tanda hubung.',
            'rating.required' => 'Silakan pilih rating.',
            'rating.between' => 'Rating harus antara 1 sampai 5.',
        ]);

        // Tambahkan IP address ke data validasi
        $validated['ip_address'] = $request->ip();

        // Cek apakah user dari IP yang sama sudah menilai bulan ini
        $alreadyRated = Rating::where('ip_address', $request->ip())
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->exists();

        if ($alreadyRated) {
            return redirect()->to(route('index') . '#feature')
                ->with('error', 'Anda sudah memberikan penilaian bulan ini!');
        }

        // Simpan data rating
        Rating::create($validated);

        return redirect()->to(route('index') . '#feature')
            ->with('success', 'Terima kasih atas penilaiannya!');
    }
}
