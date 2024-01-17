@extends('layouts.user')
@section('title','Detail Fasilitas')
@section('content')
<section>
    <div class="container aos-init aos-animate" data-aos="fade-up">
        <div class="row">
            <header class="section-header text-center">
                <h2>Fasilitas {{ $fasilitas->nama_fasilitas }}</h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
            </header>
            <div class="col-12 mt-3">
                <div class="form-group">
                    <a href="{{ route('fasilitas.index') }}" class="btn btn-primary"><i class="bi bi-arrow-left">Kembali</i></a>
                </div>
            </div>
            @php
            $fasilitas_foto = explode(',',$fasilitas->foto);
            $firstCover = reset($fasilitas_foto);
            @endphp
            @foreach ($fasilitas_foto as $image => $i)
            <div class="col-lg-4 mt-3">
                <div class="d-flex align-content-center">
                    <img src="{{ asset('storage/img/fasilitas/'. trim($i)) }}" alt="" srcset=""
                        style="width: 100%; height: 100%; margin: 1rem;">
                </div>
            </div>
            @endforeach

        </div>
</section>
@endsection
