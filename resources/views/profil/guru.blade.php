@extends('layouts.user')
@section('title','Guru')
@section('content')

<section>
    <div class="container aos-init aos-animate" data-aos="fade-up">
      <div class="row">
        <div class="col-12 text-center mb-5">
          <div class="row justify-content-center">
            <div class="col-lg-6">
              <h6 class="display-6">Guru SD Muhammadiyah 3 Samarinda </h6>
            </div>
          </div>
        </div>
        @foreach ($gurus as $guru)
        <div class="col-lg-4 text-center mb-5">
          <img src="{{ asset('storage/img/guru/'. $guru->foto) }}" alt="" class="img-fluid rounded w-50 mb-4">
          <h4>{{ $guru->name }}</h4>
          <span class="d-block mb-3 text-uppercase">{{ $guru->lulusan }}</span>
          <p>{{ $guru->desc }}</p>
        </div>
        @endforeach
      </div>
    </div>
  </section>

@endsection
