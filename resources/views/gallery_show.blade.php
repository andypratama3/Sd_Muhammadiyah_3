@extends('layouts.user')
@section('title', 'Detail Aktivitas')
@push('meta_user')
    <meta name="description" content="{!! Str::limit($gallery->name, 160) !!}">
    <meta name="keywords" content="{!! $gallery->name !!}">
    <meta name="author" content="Sekolah Kreatif SD Muhammadiyah 3 Samarinda">
    <meta name="copyright" content="Sekolah Kreatif SD Muhammadiyah 3 Samarinda">
    <meta property="og:title" content="{{ $gallery->name }}">
    <meta property="og:description" content="{!! Str::limit($gallery->name, 160) !!}">
    <meta property="og:url" content="{{ url()->current() }}">
@endpush
@section('content')
<div class="container mt-5">
    <h3 class="text-center mb-4">{{ $gallery->name }}</h3>
    <div class="row">
        @foreach ($gallery->foto as $foto)
            <div class="col-md-4 mb-4">
                <a href="{{ asset('storage/img/gallery/' . trim($foto)) }}" data-lightbox="gallery" data-title="{{ $gallery->name }}">
                    <img src="{{ asset('storage/img/gallery/' . trim($foto)) }}" class="img-fluid rounded w-100" alt="Gallery Image">
                </a>
            </div>
        @endforeach
        @if (!empty($gallery->video_id))
            <div class="col-md-12 mt-2">
                <h4 class="text-center">Video</h4>
                <iframe width="560" height="315"
                        src="https://www.youtube.com/embed/{{ $gallery->video_id }}"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                        style="border-radius: 10px; width: 100%; height: 500px;">

                </iframe>
            </div>
        @endif



    </div>

    <div class="text-center mb-2">
        <a href="{{ route('gallery.index') }}" class="btn btn-primary mt-3"><i class="fa fa-arrow-left"></i> Kembali</a>
    </div>
</div>
@endsection

