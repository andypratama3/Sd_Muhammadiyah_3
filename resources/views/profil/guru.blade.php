@extends('layouts.user')
@section('title','Guru')
@section('content')

<section>
    <div class="container aos-init aos-animate" data-aos="fade-up">
      <div class="row">
        <header class="section-header text-center">
            <h2>Guru</h2>
            <h4>SD Muhammadiyah 3 Samarinda</h4>
        </header>
        @foreach ($gurus as $guru)
        <div class="col-lg-4 text-center mb-5" style="margin-top: 40px;">
            <a href="{{ asset('storage/img/guru/'. $guru->foto) }}" class="glightbox"
                data-glightbox="title: {{ $tenagapendidikan->name }}; description: Jabatan Sebagai {{ $tenagapendidikan->name }}; type: image; effect: fade; width: 900px; height: auto; zoomable: true; draggable: true;">
                <img src="{{ asset('storage/img/guru/'. $guru->foto) }}"
                    class="img-fluid rounded w-50 mb-4" alt="">
            </a>
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
