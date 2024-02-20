@extends('layouts.user')
@section('title','Prestasi Sekolah')
@section('content')

<section class="single-post-content" style="margin-top: 95px;">
    <div class="container aos-init aos-animate" data-aos="fade-up">
        <div class="row">
            <header class="section-header text-center">
                <h2>Prestasi </h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
            </header>
            @foreach ($prestasis as $prestasi)
            <div class="col-lg-4 text-center mb-5" style="margin-top: 40px;">
                <img src="{{ asset('storage/img/prestasi/'. $prestasi->foto) }}" alt="" class="img-fluid rounded w-80 mb-4">
                <h4>{{ $prestasi->name }}</h4>
                <div class="form-group">
                    <a href="{{ route('prestasi.sekolah.show', $prestasi->slug) }}" class="btn btn-primary">Detail</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
