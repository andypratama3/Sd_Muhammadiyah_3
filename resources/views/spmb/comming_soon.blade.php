@extends('layouts.user')
@section('title', 'SPMB')

@push('meta_user')
    <meta name="description" content="SPMB SD Muhammadiyah 3 Samarinda segera dibuka. Sekolah Islam kreatif dan bernilai. Pantau informasi terbaru di sini.">
    <meta name="keywords" content="SPMB, SD Muhammadiyah 3 Samarinda, Seleksi Penerimaan Mahasiswa Baru, Sekolah Dasar Islam, Sekolah Kreatif, Pendidikan Islam">
    <meta name="author" content="SD Muhammadiyah 3 Samarinda">

    <!-- Open Graph / Facebook -->
    <meta property="og:title" content="SPMB - SD Muhammadiyah 3 Samarinda">
    <meta property="og:description" content="Seleksi Penerimaan Mahasiswa Baru SD Muhammadiyah 3 Samarinda akan segera dibuka. Sekolah Islam kreatif dan bernilai.">
    <meta property="og:image" content="{{ asset('images/spmb-coming-soon.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="SPMB - SD Muhammadiyah 3 Samarinda">
    <meta name="twitter:description" content="Seleksi Penerimaan Mahasiswa Baru SD Muhammadiyah 3 Samarinda akan segera dibuka. Sekolah Islam kreatif dan bernilai.">
    <meta name="twitter:image" content="{{ asset('images/spmb-coming-soon.jpg') }}">
@endpush

@push('css_user')
    <style>
        .animate-icon {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
                color: #ffc107;
            }
            100% {
                transform: scale(1);
            }
        }
    </style>
@endpush

@section('content')
    <div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 70vh; text-align: center;">
        <i class="fas fa-tools fa-5x text-warning mb-4 animate-icon"></i>
        <h1 class="display-4 fw-bold mb-3">Coming Soon</h1>
        <p class="lead text-muted mb-4">
            Seleksi Penerimaan Mahasiswa Baru (SPMB) untuk <strong>SD Muhammadiyah 3 Samarinda</strong> akan segera dibuka.<br>
            Kami sedang menyiapkan sistem terbaik untuk kenyamanan Anda.<br>
            Silakan kembali lagi nanti untuk informasi lengkap.
        </p>
        <a href="{{ url('/') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
        </a>
    </div>
@endsection

