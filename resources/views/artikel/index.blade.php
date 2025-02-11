@extends('layouts.user')
@section('title','Artikel')
@push('meta_user')
    <meta name="description" content="Baca artikel terbaru dan trending di Sekolah Kreatif Muhammadiyah 3 Samarinda. Temukan berita terkini, tips, dan informasi menarik lainnya.">
    <meta name="keywords" content="Sekolah Kreatif Muhammadiyah 3, Artikel, Artikel, Pendidikan, Samarinda">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="description" content="Artikel terbaru dan terpopuler dari Sekolah Kreatif Muhammadiyah 3. Temukan informasi terbaru mengenai aktivitas, prestasi, dan acara di sekolah kami.">
    <meta name="keywords" content="Artikel, Sekolah Kreatif Muhammadiyah 3, Prestasi, Acara Sekolah">
    <meta name="author" content="Nama Anda atau Nama Sekolah">
    <meta property="og:title" content="Artikel - Sekolah Kreatif Muhammadiyah 3">
    <meta property="og:description" content="Temukan Artikel terbaru dan informasi terkini tentang kegiatan dan prestasi siswa di Sekolah Kreatif Muhammadiyah 3.">
    <meta property="og:image" content="{{ asset('asset/img/SD3_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Artikel - Sekolah Kreatif Muhammadiyah 3">
    <meta name="twitter:description" content="Dapatkan Artikel terbaru dan informasi mengenai Sekolah Kreatif Muhammadiyah 3.">
    <meta name="twitter:image" content="{{ asset('asset/img/SD3_logo.png') }}">
@endpush
@push('css_user')
<style>
    .services .service-item {
        padding-top: 10px !important;
        margin: 0 !important;
    }
    .services .service-item .main-content {
        padding: 10px 20px 10px 10px !important;
    }
    .main-content img {
        border-radius: 20px;
        align-content: center;
        justify-content: center;
        margin-bottom: 20px;
        height: 200px;
    }
</style>
@endpush
@section('content')
<div class="services section" style="margin-top: 20px;">
    <div class="container">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-4 mt-2">
                    <h4 class="section-title">ARTIKEL TERBARU</h4>
                </div>
                <div class="col-md-4 mt-2">
                    <select name="categorys" class="form-control" id="category">
                        <option value="" selected>Semua Kategori</option>
                        @foreach ($categorys as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mt-2">
                   <div class="input-group">
                        <input type="text" class="form-control" id="search" value="{{ old('search') }}" name="search" placeholder="Cari Artikel">
                        <button class="btn btn-primary" id="button_search"  type="button"><i class="fa fa-search"></i></button>
                   </div>
                </div>
            </div>
            <hr>
        </div>

        <div class="row" id="target">
            @foreach ($artikels_trending as $artikel)
            <div class="col-lg-4 col-md-6" id="artikel-data">
                <div class="service-item">
                    <div class="main-content">
                        <img src="{{ asset('storage/img/artikel/'. $artikel->image) }}" alt="{{ $artikel->name }}" class="img-fluid">
                        <h4>{{ $artikel->name }}</h4>
                        <p class="category text-dark"> Kategori :
                            @foreach ($artikel->categorys as $item)
                                    {{ $item->name }}
                            @endforeach
                        </p>
                        <p class="text-dark"> {!! Str::substr( $artikel->artikel, 0, 200) !!}</p>
                        <div class="main-button">
                            <a href="{{ route('artikel.show', $artikel->slug) }}" class="btn btn-primary">Lihat Artikel <i class="fa fa-angle-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="col-md-12 d-none mt-5" id="not-found">
            <div class="container text-center">
                <h2 id="message">Artikel Tidak Di Temukan</h2>
            </div>
        </div>
    </div>
</div>
@push('js_user')
<script>
    $(document).ready(function () {
        let page = 1;
        const url = "{{ route('artikel.index') }}";
        let target = $('#target');
        let itemsLoaded = 0;
        let loading = $('#js-preloader');
        loading.hide();

        $(window).scroll(function () {
            let scrollPercentage = ($(window).scrollTop() / ($(document).height() - $(window)
                .height())) * 100;
            if (scrollPercentage >= 20 && itemsLoaded % 3 === 0) {
                if (checkInternetConnection()) {
                    // loading.show();
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
            }
        });
        $(document).on('DOMNodeInserted', function (e) {
            if ($(e.target).hasClass('col-lg-4')) {
                itemsLoaded++;
            }
        });

        function checkInternetConnection() {
            return navigator.onLine;
        }
        function getArticle()
        {
            let search = $('#search').val();
            let url = "{{ route('artikel.index') }}";

            $.ajax({
                type: "GET",
                url: url,
                data: {
                    search: search
                },
                success: function (data) {
                    if (search === '') {
                        // If search is empty, just load the original articles
                        target.removeClass('d-none').html(data);
                        $('#not-found').addClass('d-none');
                    } else if (data.status == 'error') {
                        target.addClass('d-none');
                        $('#not-found').removeClass('d-none');
                        $('#message').text(data.message);
                    } else {
                        $('#not-found').addClass('d-none');
                        target.removeClass('d-none').html(data);
                    }
                },
                error: function () {
                    target.html('<div class="alert alert-danger">Error loading articles. Please try again later.</div>');
                }
            });
        }

        $('#search').on('keyup', function () {
            getArticle();
        });

        $('#button_search').on('click', function () {
            getArticle();
        })



        $('#category').on('change', function () {
            let category = $(this).val();
            let url = "{{ route('artikel.index') }}";

            $.ajax({
                type: "GET",
                url: url,
                data: {
                    category: category
                },
                success: function (data) {
                    target.html(data);
                }
            });
        })
    });
</script>


@endpush
@endsection
