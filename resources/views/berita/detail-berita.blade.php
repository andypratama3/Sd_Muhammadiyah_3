@extends('layouts.user')
@section('title',"Berita $berita->judul")

@push('meta_user')
    <meta name="description" content="{{ Str::limit(strip_tags($berita->desc), 160) }}">
    <meta name="keywords" content="Berita, {{ $berita->judul }}, Informasi Terbaru">
    <meta property="og:title" content="{{ $berita->judul }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($berita->desc), 160) }}">
    <meta property="og:image" content="{{ asset('storage/img/berita/'. $berita->foto) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="article">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $berita->judul }}">
    <meta name="twitter:description" content="{{ Str::limit(strip_tags($berita->desc), 160) }}">
    <meta name="twitter:image" content="{{ asset('storage/img/berita/'. $berita->foto) }}">
@endpush

@push('css_user')
<style>
    .course {
        margin-top: 20px;
    }

    .title-news {
        margin-top: 20px;
        font-size: 24px;
        font-weight: bold;
        text-align: center;
    }

    .container {
        max-width: 100%;
        overflow: hidden;
    }

    .quil-wrapper {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
        padding: 10px;
        max-width: 100%;
    }

    .quil-wrapper img {
        width: 100%;
        max-width: 100%;
        height: auto;
        display: block;
        object-fit: cover;
        border-radius: 10px;
    }

    .quil-wrapper-field {
        padding: 15px;
    }

    .quil-wrapper-field p {
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: normal;
        font-size: 16px;
        line-height: 1.6;
        text-align: justify;
    }

    @media (max-width: 575px) {
        .quil-wrapper {
            flex-direction: column;
            gap: 10px;
        }

        .quil-wrapper img {
            max-width: 100%;
        }

        .quil-wrapper-field h1 {
            font-size: 20px;
        }

        .quil-wrapper-field p {
            font-size: 14px;
            line-height: 1.5;
        }
    }

    @media (min-width: 768px) {
        .quil-wrapper img {
            max-width: 48%;
        }
    }

    @media (min-width: 992px) {
        .quil-wrapper img {
            max-width: 32%;
        }
    }
</style>
@endpush

@section('content')
<div class="course">
    <div class="container mb-4">
        <div class="row">
            <div class="col-md-12" data-aos="fade-up">
                <span class="date">Di Posting Pada</span>
                <span class="mx-1">&bullet;</span>
                <span>{{ \Carbon\Carbon::parse($berita->created_at)->locale('id')->translatedFormat('d F Y') }}</span>
                <img src="{{ asset('storage/img/berita/'. $berita->foto) }}" alt="{{ $berita->judul }}" class="img-fluid">
            </div>
            <div class="col-md-12 mt-3 quil-wrapper-field">
                <h1 class="mb-2 title-news">{{ $berita->judul }}</h1>
                <div class="quil-wrapper">
                    {!! $berita->desc !!}
                </div>
            </div>
        </div>
    </div>
</div>

@push('js_user')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.quil-wrapper img').forEach((img, index) => {
            img.setAttribute('data-lightbox', 'gallery');
            img.setAttribute('data-title', img.getAttribute('alt') || 'Gambar ' + (index + 1));
        });

        if (typeof lightbox !== 'undefined') {
            lightbox.option({
                'resizeDuration': 200,
                'wrapAround': true,
                'alwaysShowNavOnTouchDevices': true
            });
        }
    });
</script>
@endpush

@endsection
