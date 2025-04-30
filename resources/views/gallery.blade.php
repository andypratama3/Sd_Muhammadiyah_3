@extends('layouts.user')
@section('title','Gallery')
@section('content')
<div class="main-banner">
    <div class="container">
        <header class="section-header text-center wow fadeInDown" data-wow-delay="0.2s">
            <h2>Gallery Aktivitas</h2>
            <h4>SD Muhammadiyah 3 Samarinda</h4>
        </header>
        <div class="form-group mt-3">
            <a href="{{ route('index') }}" class="btn btn-primary" style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important"><i class="fa fa-arrow-left"></i> Kembali</a>
        </div>

        <div class="row mt-4">
            @forelse ($gallerys as $gallery)
                @php
                    $fotos = is_array($gallery->foto) ? $gallery->foto : explode(',', $gallery->foto);
                    $firstFoto = trim($fotos[0] ?? 'default.jpg'); // default fallback
                @endphp

                <div class="col-lg-4 text-center mb-5 wow fadeInUp" data-wow-delay="0.2s">
                    <a href="{{ route('gallery.show', $gallery->slug) }}">
                        <img src="{{ asset('storage/img/gallery/' . $firstFoto) }}" alt="{{ $gallery->name }}" class="img-fluid rounded w-100 mb-4">
                    </a>
                    <h5>{{ $gallery->name }}</h5>
                </div>
            @empty
                <div class="col-lg-12 text-center mb-5">
                    <h4>Tidak ada Gallery</h4>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
