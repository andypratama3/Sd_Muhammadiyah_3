@extends('layouts.user')
@section('title','Berita')
@push('css_user')
<style>
    .blog-entry {
        display: flex;
        flex-direction: column;
        margin: 0 10 0 0;
        justify-content: center;
    }

    .blog-entry img {
        width: 100%;
        height: 250px;
        border-radius: 20px;
    }
</style>
@endpush
@section('content')
<section class="ftco-section bg-light ftco-no-pt" style="margin-top: 100px;">
    <div class="container-xl">
        <div class="row justify-content-center mb-5">
            <div class="col-md-7 heading-section text-center aos-init aos-animate" data-aos="fade-up"
                data-aos-duration="1000">
                <h2>Berita</h2>
            </div>
        </div>
        <div class="row justify-content-center" id="content">
            @foreach ($beritas as $berita)
            <div class="col-md-6 col-lg-4 d-flex mt-2" id="col-md-6">
                <div class="blog-entry justify-content-end aos-init aos-animate" data-aos="fade-up"
                    data-aos-duration="1000" data-aos-delay="100">
                    <a href="{{ route('berita.show', $berita->slug) }}">
                        <img src="{{ asset('storage/img/berita/'. $berita->foto ) }}" alt="" class="img-fluid">
                        <div class="text">
                            <p class="meta mt-2"><span><i class="fa fa-user me-1"></i>Admin</span> <span><i
                                        class="fa fa-calendar me-1"></i>{{ $berita->created_at->format('Y:m:d') }}</span>
                                <span><a href="#"><i class="fa fa-comment me-1"></i> 3 Comments</a></span></p>
                            <h3 class="heading mb-3"><a
                                    href="{{ route('berita.show', $berita->slug) }}">{{ $berita->judul }}</a>
                            </h3>
                            <p>
                                {{ Str::substr($berita->desc, 0, 50). ' ......' }}
                            </p>
                        </div>
                    </a>
                </div>
            </div>
            @endforeach
            @include('layouts.user.loading-data')
        </div>
</section>
@push('js_user')
<script>
    $(document).ready(function () {
        let page = 1;
        const url = "{{ route('berita.index') }}";
        const target = $('#content');
        let itemsLoaded = 0;
        let loading = $('.loading-data');
        loading.hide();
        function checkInternetConnection() {
            return navigator.onLine;
        }
        $(window).scroll(function () {
            let scrollPercentage = ($(window).scrollTop() / ($(document).height() - $(window)
            .height())) * 100;
            if (scrollPercentage >= 60 && itemsLoaded % 4 === 0) {
                if (checkInternetConnection()) {
                    loading.show();
                    page++;
                    $.ajax({
                        type: "GET",
                        url: url,
                        data: {
                            page: page
                        },
                        success: function (data) {
                            target.append(data);
                            // loading.hide();
                        },
                        complete: function () {
                            loading.hide();
                        }
                    });
                }
            }
        });
        $(document).on('DOMNodeInserted', function (e) {
            if ($(e.target).hasClass('col-md-6')) {
                itemsLoaded++;
            }
        });
    });
</script>
@endpush
@endsection
