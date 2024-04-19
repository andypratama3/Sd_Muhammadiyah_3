@extends('layouts.user')
@section('title','Fasilitas')
@section('content')
<section>
    <div class="container aos-init aos-animate" style="margin-top: 65px;" data-aos="fade-up">
        <div class="row">
            <header class="section-header text-center">
                <h2>Fasilitas</h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
            </header>
            @for($i = 0; $i < 100; $i++)
            @foreach ($fasilitass as $fasilitas)
            @php
                $coverArray = explode(',', rtrim($fasilitas->foto, ','));
                $firstCover = reset($coverArray);
            @endphp
            <div class="col-lg-4 text-center mb-5"  data-aos="zoom-in" style="margin-top: 40px;"  data-aos-delay="50">
                <img src="{{ asset('storage/img/fasilitas/'. $firstCover )}}" alt="" class="img-fluid rounded w-100 mb-4">
                <h4>{{ $fasilitas->nama_fasilitas }}</h4>
                <span class="d-block mb-3 text-uppercase">{{ $fasilitas->lulusan }}</span>
                <p>{{ $fasilitas->desc }}</p>

                <div class="form-group">
                    <a href="{{ route('fasilitas.show', $fasilitas->nama_fasilitas) }}" class="btn btn-primary">Lihat</a>
                </div>
            </div>
            @endforeach
            @endfor
        </div>
    </div>
</section>
@endsection
