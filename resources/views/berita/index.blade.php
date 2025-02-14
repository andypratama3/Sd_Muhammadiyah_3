@extends('layouts.user')
@section('title', 'Berita')
    <meta name="description" content="Berita terbaru dan terpopuler dari Sekolah Kreatif Muhammadiyah 3. Temukan informasi terbaru mengenai aktivitas, prestasi, dan acara di sekolah kami.">
    <meta name="keywords" content="Berita, Sekolah Kreatif Muhammadiyah 3, Prestasi, Acara Sekolah">
    <meta name="author" content="Nama Anda atau Nama Sekolah">
    <meta property="og:title" content="Berita - Sekolah Kreatif Muhammadiyah 3">
    <meta property="og:description" content="Temukan berita terbaru dan informasi terkini tentang kegiatan dan prestasi siswa di Sekolah Kreatif Muhammadiyah 3.">
    <meta property="og:image" content="{{ asset('asset/img/SD3_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Berita - Sekolah Kreatif Muhammadiyah 3">
    <meta name="twitter:description" content="Dapatkan berita terbaru dan informasi mengenai Sekolah Kreatif Muhammadiyah 3.">
    <meta name="twitter:image" content="{{ asset('asset/img/SD3_logo.png') }}">
@section('content')
<div class="container-fluid blog py-5 mt-0">
    <div class="container py-5">
        <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
            <h1 class="display-4 mb-4">Berita</h1>
            <p class="mb-4">Berita Terbaru</p>
        </div>
        <div class="row g-4 justify-content-center" id="target">
            @foreach ($beritas as $berita)
            <div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.2s">
                <div class="blog-item">
                    <div class="blog-img">
                        <img  src="{{ asset('storage/img/berita/' . $berita->foto) }}" class="img-fluid rounded-top w-100" alt="{{ $berita->judul }}">
                        <div class="blog-categiry py-2 px-4">
                            <span>Berita</span>
                        </div>
                    </div>
                    <div class="blog-content p-4">
                        <div class="blog-comment d-flex justify-content-between mb-3">
                            {{-- <div class="small"><span class="fa fa-user text-primary"></span> Martin.C</div> --}}
                            <div class="small"><span class="fa fa-calendar text-primary"></span> {{ \Carbon\Carbon::parse($berita->created_at)->diffForHumans() }}</div>
                        </div>
                        <a href="{{ route('berita.show', $berita->slug) }}" class="h4 d-inline-block mb-3">{{ $berita->judul }}</a>
                        {{-- <p class="mb-3">{!! Str::substr($berita->desc, 0, 50) !!}</p> --}}
                        <br>
                        <a href="{{ route('berita.show', $berita->slug) }}" class="btn p-0">Lihat Berita  <i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@push('js_user')
<script>
    $(document).ready(function () {
        let page = 1;
        const url = "{{ route('berita.index') }}";
        const target = $('#target');
        let itemsLoaded = 0;
        let loading = $('#js-preloader');

        loading.hide();

        $(window).scroll(function () {
            let scrollPercentage = ($(window).scrollTop() / ($(document).height() - $(window)
            .height())) * 100;
            if (scrollPercentage >= 80 && itemsLoaded % 3 === 0 && checkInternetConnection()) {
                loading.show();
                page++;
                $.ajax({
                    type: "GET",
                    url: url,
                    cache: false,
                    data: {
                        page: page
                    },
                    success: function (data) {
                        target.append(data);
                    },
                    complete: function () {
                        loading.hide();
                    }
                });
            }
        });

        $(document).on('DOMNodeInserted', function (e) {
            if ($(e.target).hasClass('col-lg-6')) {
                itemsLoaded++;
            }
        });

        function checkInternetConnection() {
            return navigator.onLine;
        }
    });
</script>
@endpush
@endsection
