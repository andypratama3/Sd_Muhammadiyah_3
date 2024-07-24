@extends('layouts.user_new')
@section('title','Detail Fasilitas')
@section('content')
<section>
    <div class="container aos-init aos-animate mb-5" data-aos="fade-up">
        <div class="row">
            <header class="section-header text-center">
                <h2>Fasilitas {{ $fasilitas->nama_fasilitas }}</h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
            </header>
            <div class="col-12 mt-3">
                <div class="form-group">
                    <a href="{{ route('fasilitas.index') }}" class="btn btn-primary" style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important"><i class="fa fa-arrow-left"></i> Kembali</a>
                </div>
            </div>

            @php
            $fasilitas_foto = explode(',',$fasilitas->foto);
            $firstCover = reset($fasilitas_foto);
            @endphp
            @foreach ($fasilitas_foto as $image => $i)
            <div class="col-lg-4 mt-3" data-aos="flip-left">
                <div class="d-flex align-content-center">
                    <a href="{{ asset('storage/img/fasilitas/'. trim($i)) }}" class="glightbox" title="fasilitas : {{ $fasilitas->nama_fasilitas }}">
                    <img src="{{ asset('storage/img/fasilitas/'. trim($i)) }}" alt="" srcset=""
                        style="width: 100%; height: 100%; margin: 1rem; border-radius: 20px;">
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
