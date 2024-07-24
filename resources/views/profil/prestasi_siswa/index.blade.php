@extends('layouts.user_new')
@section('title','Prestasi Siswa')
@section('content')

<div class="hero-banner" style="margin-top: 95px;">
    <div class="container aos-init aos-animate" data-aos="fade-up">
        <div class="row">
            <header class="section-header text-center">
                <h2>Prestasi Siswa</h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
            </header>
            @foreach ($prestasis as $prestasi)
            <div class="col-lg-4 text-center mb-5" style="margin-top: 40px;">
                <img src="{{ asset('storage/img/prestasi/'. $prestasi->foto) }}" alt=""
                    class="img-fluid rounded w-80 mb-4">
                <h4>{{ $prestasi->name }}</h4>
                <div class="form-group mt-4">
                    <a href="{{ route('prestasi.siswa.show', $prestasi->slug) }}" class="btn btn-primary" style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important">Lihat</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
