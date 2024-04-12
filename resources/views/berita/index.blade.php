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
        <div class="row justify-content-center">
            @foreach ($beritas as $berita)

            <div class="col-md-6 col-lg-4 d-flex mt-2">
                <div class="blog-entry justify-content-end aos-init aos-animate" data-aos="fade-up"
                    data-aos-duration="1000" data-aos-delay="100">
                    <a href="{{ route('berita.show', $berita->slug) }}">
                        <img src="{{ asset('storage/img/berita/'. $berita->foto ) }}" alt="" class="img-fluid">
                        <div class="text">
                            <p class="meta mt-2"><span><i class="fa fa-user me-1"></i>Admin</span> <span><i
                                        class="fa fa-calendar me-1"></i>{{ $berita->created_at->format('Y:m:d') }}</span> <span><a href="#"><i
                                            class="fa fa-comment me-1"></i> 3 Comments</a></span></p>
                            <h3 class="heading mb-3"><a href="{{ route('berita.show', $berita->slug) }}">{{ $berita->judul }}</a>
                            </h3>
                            <p>
                                {{ Str::substr($berita->desc, 0, 50). ' ......' }}
                            </p>
                        </div>
                    </a>
                </div>
            </div>
            @endforeach
            <div class="container mt-5">
                <div class="form-group text-center">
                    <button class="btn btn-primary load-more">Load More</button>
                </div>
            </div>
        </div>
</section>
@push('js_user')
    <script>
        $(document).ready(function () {
            let item_berita = $('.blog-entry');
            item_berita.slice(0, 3).show();
            $('.load-more').on('click', function () {

            });
        });
    </script>
@endpush
@endsection
