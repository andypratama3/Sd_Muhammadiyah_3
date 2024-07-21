@extends('layouts.user_new')
@section('title','Artikel')
@push('css_user')
    <style>
        .services .service-item {
            border: 2px solid red;
            padding-top: 10px !important;
            margin: 0 !important;
        }
    </style>
@endpush
@section('content')
<div class="services section" style="margin-top: 100px;">
    <div class="container">
        
        <h4 class="section-title">
            ARTIKEL TERBARU</h4>
        <div class="row" >
            <div class="col-lg-4 col-md-6">
                <div class="service-item">

                    {{-- <div class="icon">
                        <img src="http://127.0.0.1:8000/asset_new/images/service-01.png" alt="Akreditas">
                    </div> --}}
                    <div class="main-content">
                        <h4>Akreditas</h4>
                        <p>Whenever you need free templates in HTML CSS, you just remember TemplateMo website.</p>
                        <div class="main-button">
                            <a href="#">Read More</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<section id="posts" class="posts" style="margin-top: 95px;">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                @foreach ($artikels_trending as $artikel)
                <div class="post-entry-1-lg" style="margin-right: 20px; margin-top: 20px;">
                    <a href="{{ route('artikel.show', $artikel->slug) }}">
                        {{-- <img src="{{ asset('storage/img/artikel/'. $artikel->image) }}" alt="" class="img-fluid">
                        --}}
                        <img src="{{ asset('storage/img/berita/Berita_test_20240412172357.jpg') }}" alt=""
                        class="img-fluid">
                    </a>
                    <div class="post-meta mt-2"><span class="date">Di Posting</span> <span class="mx-1">&bullet;</span>
                        <span>{{ $artikel->created_at->formatLocalized('%A %d %B %Y') }}</span></div>
                    <h2><a href="{{ route('artikel.show', $artikel->slug) }}">{{ $artikel->name }}</a></h2>
                    @php
                    $content = strip_tags($artikel->artikel);
                    $secondParagraph = substr($content, strpos($content, '</p>') + 4);
                    @endphp

                    <p>{!! $secondParagraph !!}</p>
                    {{-- <p>{!! substr(strip_tags($artikel->artikel), 0, strpos(strip_tags($artikel->artikel), '</p>') + 4) !!}</p> --}}

                    <div class="d-flex align-items-center author">
                        <div class="name mt-4">
                            <h3 class="author">Kategori : </h3>
                            @foreach ($artikel->categorys as $category)
                            <span class="author badge bg-primary">{{ $category->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="col-lg-6">
                <div class="row g-5 target" id="target">
                    @foreach ($artikel_not_trending as $artikel)
                    @if (!$artikels_trending->contains('id', $artikel->id))
                    <div class="col-lg-4 border-start custom-border" data-aos="zoom-in-down" data-duration="200">
                        <div class="post-entry-1">
                            <a href="{{ route('artikel.show', $artikel->slug) }}">
                                {{-- <img src="{{ asset('storage/app/public/img/artikel/'. $artikel->image) }}" alt=""
                                class="img-fluid"> --}}
                                <img src="{{ asset('storage/img/berita/Berita_test_20240412172357.jpg') }}" alt=""
                                    class="img-fluid">
                            </a>
                            <div class="post-meta"><span class="date">Di Posting</span>
                                <span class="mx-1">&bullet;</span>
                                <span class="mt-2">{{ $artikel->created_at->formatLocalized('%A %d %B %Y') }}</span>
                            </div>
                            <h2><a href="{{ route('artikel.show', $artikel->slug) }}">{{ $artikel->name }}</a></h2>
                        </div>
                    </div>
                    @endif
                    @endforeach
                    @include('layouts.user.loading-data')
                </div>
            </div>
            <!-- Trending Section -->
            <div class="col-md-2">
                <!-- ======= Sidebar ======= -->
                <div class="aside-block">
                    <ul class="nav nav-pills custom-tab-nav mb-4" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-latest-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-latest" type="button" role="tab" aria-controls="pills-latest"
                                aria-selected="true">Latest</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-trending-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-trending" type="button" role="tab" aria-controls="pills-trending"
                                aria-selected="false">Trending</button>
                        </li>

                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-latest" role="tabpanel"
                            aria-labelledby="pills-latest-tab">
                            @foreach ($latest_artikel as $latest)
                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta">
                                    <span class="date"></span>
                                    <span class="mx-1">&bullet;</span>
                                    <span>{{ $latest->created_at->formatLocalized('%A %d %B %Y') }}</span></div>
                                <h2 class="mb-2"><a
                                        href="{{ route('artikel.show', $latest->slug) }}">{{ $latest->name }}</a></h2>
                                @foreach ($latest->categorys as $category)
                                <span class="author mb-3 d-block">{{ $category->name }}</span>
                                @endforeach
                            </div>
                            @endforeach
                        </div>

                        <!-- Trending -->
                        <div class="tab-pane fade" id="pills-trending" role="tabpanel"
                            aria-labelledby="pills-trending-tab">
                            @foreach ($artikel_trending_list as $trending)
                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta">
                                    <span class="date"></span>
                                    <span class="mx-1">&bullet;</span>
                                    <span>{{ $trending->created_at->formatLocalized('%A %d %B %Y') }}</span></div>
                                <h2 class="mb-2"><a
                                        href="{{ route('artikel.show', $trending->slug) }}">{{ $trending->name }}</a>
                                </h2>
                                @foreach ($trending->categorys as $category)
                                <span class="author mb-3 d-block">{{ $category->name }}</span>
                                @endforeach
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- End .row -->
    </div>
</section> <!-- End Post Grid Section -->
@push('js_user')
<script>
       $(document).ready(function () {
        let page = 1;
        const url = "{{ route('artikel.index') }}";
        const target = $('#target');
        let itemsLoaded = 0;
        let loading = $('.loading-data');
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
    });
</script>


@endpush
@endsection
