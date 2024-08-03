@extends('layouts.user')
@section('title','Ekstrakurikuler')
@section('content')
<section>
    <div class="container aos-init aos-animate" style="margin-top: 20px;" data-aos="fade-up">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('index') }}" class="btn btn-primary float-start" style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important"><i class="fas fa-arrow-left"></i>Kembali</a>
            </div>
            <header class="section-header text-center">
                <h2>Ekstrakurikuler</h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
            </header>
            @foreach ($ekstrakurikulers as $ekstrakurikuler)
            @php
                $coverArray = explode(',', rtrim($ekstrakurikuler->foto, ','));
                $firstCover = reset($coverArray);
            @endphp
            <div class="col-lg-4 text-center mb-5 wow fadeInUp" data-wow-delay="0.2s" style="margin-top: 40px;">
                <img src="{{ asset('storage/img/ekstrakurikuler/'. $firstCover )}}" alt="" class="img-fluid rounded w-100 mb-4">
                <h4>{{ $ekstrakurikuler->name }}</h4>
                <span class="d-block mb-3 text-uppercase">{{ $ekstrakurikuler->lulusan }}</span>
                <p>{{ $ekstrakurikuler->desc }}</p>

                <div class="form-group">
                    <a href="{{ route('esktrakurikuler.show', $ekstrakurikuler->name) }}" class="btn btn-primary">Lihat</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
