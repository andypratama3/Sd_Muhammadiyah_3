@extends('layouts.user')
@section('title','Prestasi')
@section('content')

<section class="single-post-content" style="margin-top: 95px;">
    <div class="container aos-init aos-animate" data-aos="fade-up">
        <div class="row">
            <header class="section-header text-center">
                <h2>Prestasi Sekolah</h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
            </header>
            <div class="col-md-12 mt-2 justify-center" style="border: 2px solid red;">
                <img src="{{ asset('storage/img/prestasi/'. $prestasi->foto) }}" alt="" class="img-fluid rounded w-50 mb-4">
                <h4>{{ $prestasi->name }}</h4>
            </div>
        </div>
    </div>
</section>

@endsection
