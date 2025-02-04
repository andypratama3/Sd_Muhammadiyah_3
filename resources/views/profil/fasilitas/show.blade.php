@extends('layouts.user')
@section('title','Detail Fasilitas')
@section('content')
<section>
    <div class="container mb-5">
        <div class="row">
            <div class="col-md-12 mt-3 wow fadeInDown" data-wow-delay="0.2s">
                <div class="form-group">
                    <a href="{{ route('fasilitas.index') }}" class="btn btn-primary btn-sm" style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important"><i class="fa fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="col-md-12 mt-3 wow fadeInDown" data-wow-delay="0.2s">
                <header class="section-header text-center">
                    <h2>{{ $fasilitas->nama_fasilitas }}</h2>
                    <h4>SD Muhammadiyah 3 Samarinda</h4>
                    <hr>
                </header>

            </div>

            @php
            $fasilitas_foto = explode(',',$fasilitas->foto);
            $firstCover = reset($fasilitas_foto);
            @endphp
            @foreach ($fasilitas_foto as $image => $i)
            <div class="col-md-4 mt-3 wow fadeInUp" data-wow-delay="0.2s">
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
