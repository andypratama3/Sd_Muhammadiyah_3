@extends('layouts.user')
@section('title','Prestasi Siswa')
@section('content')

<div class="hero-banner" style="margin-top: 20px;">
    <div class="container">
        <div class="row">
            <div class="col-md-12 wow fadeInLeft" data-wow-delay="0.2s">
                <a href="{{ route('index') }}" class="btn btn-primary float-start btn-sm" style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important"><i class="fas fa-arrow-left"></i>Kembali</a>
            </div>
            <div class="mt-3 col-md-12 wow fadeInDown" data-wow-delay="0.2s">
                <header class="text-center section-header">
                    <h2>Prestasi Siswa</h2>
                    <h6>SD Muhammadiyah 3 Samarinda</h6>
                </header>
            </div>
            @forelse ($prestasis as $prestasi)
            <div class="mb-5 text-center col-lg-4 wow fadeInUp" data-wow-delay="0.2s" style="margin-top: 40px;" >
                <img src="{{ asset('storage/img/prestasi/'. $prestasi->foto) }}" alt=""
                    class="mb-4 rounded img-fluid w-80">
                <h6>{{ $prestasi->name }}</h6>
                <div class="mt-4 form-group">
                    <a href="{{ route('prestasi.siswa.show', $prestasi->slug) }}" class="btn btn-primary" style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important">Lihat</a>
                </div>
            </div>
            @empty
                <div class="mb-5 text-center col-lg-12 wow fadeInUp" data-wow-delay="0.2s" style="margin-top: 40px;" data-aos-delay="50">
                    <h4>Tidak ada Prestasi Siswa</h4>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
