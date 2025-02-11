@extends('layouts.user')
@section('title','Detail')
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
</style>
@endpush
@section('content')
<div class="course">
    <div class="container mb-4">
        <div class="row">
            <div class="col-md-12" data-aos="fade-up">
                <span class="date">Di Posting Pada</span> <span class="mx-1">&bullet;</span>
                    <span> {{ $berita->created_at }}</span>
                <img src="{{ asset('storage/img/berita/'. $berita->foto) }}" alt="{{ $berita->judul }}" class="img-fluid">
            </div>
            <div class="col-md- 12 mt-3">
                <h1 class="mb-2" class="title-news">{{ $berita->judul }}</h1>
                <p>{!! $berita->desc !!}</p>
            </div>
        </div>
    </div>
</div>
@endsection
