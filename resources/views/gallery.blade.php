@extends('layouts.user')
@section('title','Gallery')
@section('content')
<div class="main-banner">
    <div class="container" >
        <div class="row">
            <header class="section-header text-center wow fadeInDown" data-wow-delay="0.2s">
                <h2>Gallery</h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
            </header>
            <div class="col-12 mt-3 wow fadeInLeft" data-wow-delay="0.2s">
                <div class="form-group">
                    <a href="{{ route('index') }}" class="btn btn-primary" style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important"><i class="fa fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
                @forelse ($gallerys as $gallery)
                <div class="col-lg-4 text-center mb-5 wow fadeInUp" data-wow-delay="0.2s" style="margin-top: 40px;" data-aos-delay="50">
                    <a href="{{ asset('storage/img/gallery/'. $gallery->foto) }}" data-lightbox="{{ $gallery->foto }}" class=""><i class="fas fa-link text-white"></i>
                    <img src="{{ asset('storage/img/gallery/'. $gallery->foto )}}" alt=""
                        class="img-fluid rounded w-100 mb-4">
                    </a>
                    <h4>{{ $gallery->name }}</h4>
                </div>
            @empty
            <div class="col-lg-12 text-center mb-5 wow fadeInUp" data-wow-delay="0.2s" style="margin-top: 40px;" data-aos-delay="50">
                <h4>Tidak ada Gallery</h4>
            @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
