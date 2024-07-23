@extends('layouts.user_new')
@section('title','Detail')
@push('css_user')
<style>
    .course {
        margin-top: 100px;
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
            <div class="col-md-8" data-aos="fade-up">
                <span class="date">Di Posting Pada</span> <span class="mx-1">&bullet;</span>
                    <span> {{ $berita->created_at }}</span>
                <img src="{{ asset('storage/img/berita/'. $berita->foto) }}" alt="" class="img-fluid">
            </div>
            <div class="col-md-4 mt-3">
                <h1 class="mb-2" class="title-news">{{ $berita->judul }}</h1>
                <p>{!! $berita->desc !!}</p>
            </div>
        </div>
    </div>
</div>
@endsection
