@extends('layouts.user')
@section('title','Prestasi Siswa')
@section('content')

<section class="single-post-content" style="margin-top: 20px;">
    <div class="container aos-init aos-animate" data-aos="fade-up">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('prestasi.siswa.index') }}" class="btn btn-primary float-start" style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important"><i class="fas fa-arrow-left"></i>Kembali</a>
            </div>
            <header class="section-header text-center">
                <h2> Prestasi Siswa</h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
            </header>
            <div class="col-md-12 mt-2 text-center">
                <img src="{{ asset('storage/img/prestasi/'. $prestasi->foto) }}" alt="" class="img-fluid rounded w-50 mb-4">
                <h4>{{ $prestasi->name }}</h4>
            </div>
            <div class="row description">
                <p class="">{!! $prestasi->description !!}</p>
            </div>
        </div>
    </div>
</section>

@endsection
