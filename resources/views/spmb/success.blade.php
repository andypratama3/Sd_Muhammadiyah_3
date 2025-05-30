@extends('layouts.user')
@section('title', 'Pendaftaran SPMB Berhasil')

@push('meta_user')
    <meta name="description" content="SPMB SD Muhammadiyah 3 Samarinda segera dibuka. Sekolah Islam kreatif dan bernilai. Pantau informasi terbaru di sini.">
    <meta name="keywords" content="SPMB, SD Muhammadiyah 3 Samarinda, Sistem Penerimaan Murid Baru, Sekolah Dasar Islam, Sekolah Kreatif, Pendidikan Islam">
    <meta name="author" content="SD Muhammadiyah 3 Samarinda">

    <!-- Open Graph / Facebook -->
    <meta property="og:title" content="SPMB - SD Muhammadiyah 3 Samarinda">
    <meta property="og:description" content="Sistem Penerimaan Murid Baru SD Muhammadiyah 3 Samarinda akan segera dibuka. Sekolah Islam kreatif dan bernilai.">
    <meta property="og:image" content="{{ asset('images/spmb-coming-soon.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="SPMB - SD Muhammadiyah 3 Samarinda">
    <meta name="twitter:description" content="Sistem Penerimaan Murid Baru SD Muhammadiyah 3 Samarinda akan segera dibuka. Sekolah Islam kreatif dan bernilai.">
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
                /* color: #ffc107; */
            }
            100% {
                transform: scale(1);
            }
        }
    </style>
@endpush

@section('content')
    <div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 70vh; text-align: center;">
        <i class="fas fa-check fa-5x text-success mb-4 animate-icon"></i>
        <h1 class="display-4 fw-bold mb-3">Pendaftaran SPMB Berhasil </h1>
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title m-0">Detail Pendaftaran</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <label for="">Nama Lengkap Anak</label>
                        <p>{{ $spmb->nama }}</p>
                        <hr>
                    </div>
                    <div class="col-md-12">
                        <label for="">Tanggal Lahir</label>
                        <p>{{ \Carbon\Carbon::parse($spmb->tanggal_lahir)->format('d-m-Y') }}</p>
                        <hr>
                    </div>
                    <div class="col-md-12">
                        <label for="">Jenis Kelamin</label>
                        <p>{{ $spmb->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                        <hr>
                    </div>
                    <div class="col-md-12">
                        <label for="">HP</label>
                        <p>{{ $spmb->phone }}</p>
                        <hr>
                    </div>
                    <div class="col-md-12">
                        <label for="">Status Pembayaran</label>
                        <p>
                            @if ($spmb->status_pembayaran == 'pending')
                                <span class="badge bg-warning">Belum Lunas</span>
                            @elseif ($spmb->status_pembayaran == 'settlement')
                                <span class="badge bg-success">Lunas</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <a href="{{ url('/') }}" class="btn btn-outline-primary m-4">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
        </a>
    </div>
@endsection

