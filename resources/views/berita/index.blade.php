@extends('layouts.user_new')
@section('title', 'Berita')

@section('content')
<section class="section courses mt-5" id="courses">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="section-heading">
                    <h2>Berita</h2>
                </div>
            </div>

            <div class="row m-0" id="target">
                @foreach ($beritas as $berita)
                <div class="col-lg-4 col-md-6 align-self-center mb-30 event_outer col-md-6 design">
                    <div class="events_item">
                        <div class="thumb">
                            <a href="{{ route('berita.show', $berita->slug) }}">
                                <img src="{{ asset('storage/img/berita/' . $berita->foto) }}" alt="">
                                <span class="price">
                                    <h6><em><code>*</code></em>New</h6>
                                </span>
                        </div>
                        <div class="down-content">
                            <span class="author">{{ \Carbon\Carbon::parse($berita->created_at)->diffForHumans() }}</span>
                            <h4>{{ $berita->judul }}</h4>
                            <p>{{ Str::substr($berita->desc, 0, 50) . ' ......' }}</p>
                        </div>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</section>
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
