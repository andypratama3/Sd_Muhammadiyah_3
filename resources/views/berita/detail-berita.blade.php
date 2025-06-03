@extends('layouts.user')
@section('title',"Berita $berita->judul")
@push('meta_user')
    <meta name="description" content="{{ Str::limit(strip_tags($berita->desc), 160) }}">
    <meta name="keywords" content="Berita, {{ $berita->judul }}, Informasi Terbaru">
    <meta property="og:title" content="{{ $berita->judul }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($berita->desc), 200) }}">
    <meta property="og:image" content="{{ asset('storage/img/berita/'. $berita->foto) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="{{ Str::limit(strip_tags($berita->desc), 200) }}">
    <meta name="twitter:title" content="{{ $berita->judul }}">
    <meta name="twitter:description" content="{{ Str::limit(strip_tags($berita->desc), 200) }}">
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
    }


    .quil-wrapper-field {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 2px;
        overflow: hidden;
        max-width: 100%;
    }

    .quil-wrapper-field img {
        max-width: 100%;
        max-height: fit-content;
        display: block;
        border-radius: 10px;
    }


    /* Pembungkus konten berita */
    .quil-wrapper-field {
        font-size: 16px;
        line-height: 1.6;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* Memastikan list tetap memiliki format yang baik */
    .quil-wrapper-field ul,
    .quil-wrapper-field ol {
        margin-left: 20px;
        padding-left: 20px;
    }

    .quil-wrapper-field ul li,
    .quil-wrapper-field ol li {
        margin-bottom: 5px;
    }

    @media (max-width: 575px) {
        .quil-wrapper {
            flex-direction: column;
            align-items: center;
        }

        .quil-wrapper img {
            padding: 10px;
            max-width: 100%;
            height: auto;
            margin: 4px auto;
            box-sizing: border-box;
        }

        .quil-wrapper-field h1 {
            font-size: 18px;
            text-align: center;
        }

        .quil-wrapper-field p {
            font-size: 14px;
            line-height: 1.5;
            text-align: justify;
        }
    }



    @media (min-width: 768px) {
        .quil-wrapper-field img {
            max-width: calc(50% - 10px);
            height: 200px;
            margin: 2px 5px 2px;
            display: inline-block;
            object-fit: cover;
            border-radius: 10px;
        }
    }

    @media (min-width: 992px) {
        .quil-wrapper-field img {
            display: inline-block;
            /* margin: 10px; */
            width: 100%;
            height: max-content;
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
                <span class="mx-1"><bi class="bi bi-eye"></bi> {{ $berita->views }} Pengunjung</span>
                <figure class="text-center">
                    <img src="{{ asset('storage/img/berita/'. $berita->foto) }}" alt="{{ $berita->judul }}" class="img-fluid text-center" style="border-radius: 10px;">
                </figure>
            </div>

            <div class="col-md-12 mt-3 quil-wrapper-field">
                <h1 class="mb-2 title-news text-start">{{ $berita->judul }}</h1>
                <div class="quil-wrapper-content">{!! $berita->desc !!}</div>
            </div>
        </div>
    </div>
</div>

@push('js_user')
<script>
    document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.quil-wrapper-content img').forEach((img, index) => {
        img.dataset.lightbox = 'Berita';
        img.dataset.title = img.alt || `Gambar ${index + 1}`;
    });

    if (window.lightbox) {
        lightbox.option({
            resizeDuration: 200,
            wrapAround: true,
            alwaysShowNavOnTouchDevices: true
        });
    }
});
</script>
@endpush

@endsection
