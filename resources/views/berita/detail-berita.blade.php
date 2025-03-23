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

    .course .title-news {
        margin-top: 400px;
    }

    img {
        border-radius: 10px;

    }

    .quil-wrapper {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px; /* Jarak antar gambar */
    }

    .quil-wrapper img {
        width: 100%;
        max-width: 100%; /* Pastikan gambar tidak lebih besar dari kontainernya */
        height: auto;
        margin-bottom: 10px; /* Jarak antar gambar ke bawah */
        padding: 5px; /* Memberikan ruang dalam gambar */
        border-radius: 10px;
        display: block;
    }

    /* Responsive Layout */
    @media (min-width: 576px) { /* Untuk layar kecil (mobile) */
        .quil-wrapper img {
            max-width: 90%; /* Sedikit lebih kecil agar ada ruang di sisi */
        }
    }

    @media (min-width: 768px) { /* Untuk tablet */
        .quil-wrapper img {
            max-width: 48%; /* Dua gambar per baris */
        }
    }

    @media (min-width: 992px) { /* Untuk laptop dan desktop */
        .quil-wrapper img {
            max-width: 32%; /* Tiga gambar per baris */
        }
    }





</style>
@endpush
@section('content')
<div class="course">
    <div class="container mb-4">
        <div class="row">
            <div class="col-md-12" data-aos="fade-up">
                <span class="date">Di Posting Pada</span> <span class="mx-1">&bullet;</span>
                    <span>{{ \Carbon\Carbon::parse($berita->created_at)->locale('id')->translatedFormat('d F Y') }}</span>

                <img src="{{ asset('storage/img/berita/'. $berita->foto) }}" alt="{{ $berita->judul }}" class="img-fluid">
            </div>
            <div class="col-md- 12 mt-3" class="quil-wrapper-field">
                <h1 class="mb-2" class="title-news">{{ $berita->judul }}</h1>
                <p>{!! $berita->desc !!}</p>
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
    });
</script>
@endpush

@endsection
