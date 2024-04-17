@extends('layouts.user')
@section('title','Jadwal')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

@section('content')

<section>
    <div class="container" data-aos="fade-up">
      <div class="row">
        <div class="col-12 text-center mb-5">
          <div class="row justify-content-center">
            <div class="col-lg-6">
              <h2 class="display-4">Jadwal Sekolah</h2>
              <p>Jadwal Sekolah Kini Bisa Di Lihat Secara Online</p>
            </div>
          </div>
        </div>
        @foreach ($kelass as $kelas)
        <div class="col-lg-4 text-center mb-5">
          <img src="{{ asset('assets/img/SD3_logo.png') }}" alt="" class="img-fluid w-50 mb-4">
          <h4>{{ $kelas->name }}</h4>
          <span class="d-block mb-3 text-uppercase"><a href="{{ route('jadwal.show', $kelas->name) }}" class="btn btn-primary btn-sm">Pilih</a></span>
        </div>
        @endforeach
      </div>
    </div>
</section>

@endsection
