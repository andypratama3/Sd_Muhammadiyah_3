@extends('layouts.user')
@section('title', 'PPDB')

@section('content')
    <div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 70vh; text-align: center;">
        <i class="fas fa-tools fa-5x text-warning mb-4 animate-icon"></i>
        <h1 class="display-4 fw-bold mb-3">Coming Soon</h1>
        <p class="lead text-muted mb-4">Pendaftaran Peserta Didik Baru (PPDB) akan segera tersedia. Silakan kembali lagi nanti.</p>
        <a href="{{ url('/') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
        </a>
    </div>

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
@endsection
