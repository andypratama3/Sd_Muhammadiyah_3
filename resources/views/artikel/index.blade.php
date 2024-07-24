@extends('layouts.user_new')
@section('title','Artikel')
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
<div class="services section" style="margin-top: 100px;">
    <div class="container">
        <h4 class="section-title">
            ARTIKEL TERBARU</h4>
        <div class="row" id="target">
            @foreach ($artikels_trending as $artikel)
            <div class="col-lg-4 col-md-6">
                <div class="service-item">
                    <div class="main-content">
                        <img src="{{ asset('storage/img/artikel/'. $artikel->image) }}" alt="foto-artikel" class="img-fluid">
                        <h4>{{ $artikel->name }}</h4>
                        <p class="category"> Kategori :
                            @foreach ($artikel->categorys as $item)
                                    {{ $item->name }}
                            @endforeach
                        </p>
                        <p>{!!  $artikel->artikel !!}</p>
                        <div class="main-button">
                            <a href="{{ route('artikel.show', $artikel->slug) }}">Lihat Artikel</a>
                        </div>
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
        const url = "{{ route('artikel.index') }}";
        const target = $('#target');
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
    });
</script>


@endpush
@endsection
