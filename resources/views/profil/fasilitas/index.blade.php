@extends('layouts.user')
@section('title','Sarana & Prasarana')
@section('content')
<div class="main-banner">
    <div class="container">
        <div class="row">
            <header class="section-header text-center wow fadeInDown" data-wow-delay="0.2s">
                <h2>Sarana & Prasarana</h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
                <hr>
            </header>

            @forelse ($fasilitass as $fasilitas)
            @php
            $coverArray = explode(',', rtrim($fasilitas->foto, ','));
            $firstCover = reset($coverArray);
            @endphp
            <div class="col-lg-4 text-center mb-5 wow fadeInUp" data-wow-delay="0.2s" data-aos="zoom-in" style="margin-top: 40px;" data-aos-delay="50">
                <img src="{{ asset('storage/img/fasilitas/'. $firstCover )}}" alt=""
                    class="img-fluid rounded w-100 mb-4">
                <h4>{{ $fasilitas->nama_fasilitas }}</h4>
                <span class="d-block mb-3 text-uppercase">{{ $fasilitas->lulusan }}</span>
                <p>{{ $fasilitas->desc }}</p>

                <div class="form-group">
                    <a href="{{ route('fasilitas.show', $fasilitas->nama_fasilitas) }}"
                        class="btn btn-primary" style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important">Lihat</a>
                </div>
            </div>
            @empty
                <div class="col-lg-12 text-center mb-5 wow fadeInUp" data-wow-delay="0.2s" style="margin-top: 40px;" data-aos-delay="50">
                    <h4>Tidak ada Sarana & Prasarana</h4>
                </div>
            @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
