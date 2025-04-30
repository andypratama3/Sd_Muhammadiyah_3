@extends('layouts.user')
@section('title', 'Detail Aktivitas')
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
    </div>
    <div class="text-center mb-2">
        <a href="{{ route('gallery.index') }}" class="btn btn-primary mt-3"><i class="fa fa-arrow-left"></i> Kembali</a>
    </div>
</div>
@endsection

