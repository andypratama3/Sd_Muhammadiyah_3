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
<!-- Lightbox CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">

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

    .gallery {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        padding: 10px;
    }

    .gallery img {
        width: 100%;
        height: auto;
        border-radius: 10px;
        display: block;
        object-fit: cover;
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
        .gallery {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .quil-wrapper-field h1 {
            font-size: 20px;
        }

        .quil-wrapper-field p {
            font-size: 14px;
            line-height: 1.5;
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
                <div class="gallery">
                    {!! $berita->desc !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js_user')
<!-- Lightbox JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.gallery img').forEach((img, index) => {
            let wrapper = document.createElement("a");
            wrapper.href = img.src;
            wrapper.setAttribute('data-lightbox', 'gallery');
            wrapper.setAttribute('data-title', img.getAttribute('alt') || 'Gambar ' + (index + 1));
            img.parentNode.insertBefore(wrapper, img);
            wrapper.appendChild(img);
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
