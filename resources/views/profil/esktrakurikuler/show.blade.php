@extends('layouts.user')
@section('title','Detail ekstrakurikuler')
@section('content')
<section>
    <div class="container aos-init aos-animate mb-5" style="margin-top: 20;" data-aos="fade-up">
        <div class="row">
            <header class="section-header text-center">
                <h2>Ekstrakurikuler {{ $ekstrakurikuler->name }}</h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
            </header>
            <div class="col-12 mt-3">
                <div class="form-group">
                    <a href="{{ route('esktrakurikuler.index') }}" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
            </div>
            @php
            $ekstrakurikuler_foto = explode(',',$ekstrakurikuler->foto);
            $firstCover = reset($ekstrakurikuler_foto);
            @endphp
            @foreach ($ekstrakurikuler_foto as $image => $i)
            <div class="col-lg-4 mt-3 wow fadeInUp" data-wow-delay="0.2s" data-aos="zoom-in">
                <div class="d-flex align-content-center">
                    <a href="{{ asset('storage/img/ekstrakurikuler/'. trim($i)) }}" class="glightbox" title="Esktrakurikuler : {{ $ekstrakurikuler->name }}">
                        <img src="{{ asset('storage/img/ekstrakurikuler/'. trim($i)) }}" alt="" srcset=""
                            style="width: 100%; height: 100%; margin: 1rem; border-radius: 15px;">
                        </a>

                </div>
            </div>
            @endforeach
        </div>
</section>
@endsection
