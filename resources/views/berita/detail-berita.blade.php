@extends('layouts.user')
@section('title','Detail')
@push('css_user')
    <style>
        img{
            border-radius: 10px;
        }
    </style>
@endpush
@section('content')
<section class="single-post-content" style="margin-top: 94px;">
    <div class="container">
        <div class="row">
            <div class="col-md-9 post-content" data-aos="fade-up">
                <!-- ======= Single Post Content ======= -->
                <div class="single-post">
                    <div class="post-meta"><span class="date">Di Posting Pada</span> <span class="mx-1">&bullet;</span>
                        <span> {{ $berita->created_at }}</span></div>
                        <figure class="my-4">
                            <img src="{{ asset('storage/img/berita/'. $berita->foto) }}" alt="" class="img-fluid">
                            </figcaption>
                        </figure>
                    <h1 class="mb-2">{{ $berita->judul }}</h1>
                </div>
            </div>
            @include('layouts.user.sidebar')
        </div>
    </div>
</section>
@endsection
