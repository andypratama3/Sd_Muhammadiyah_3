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
        <div class="row mb-4">
            <input type="text" placeholder="Cari Jadwal" class="form-control">
            <i class="bi bi-search"></i>
        </div>
        @foreach ($jadwals as $jadwal)
        <div class="col-lg-4 text-center mb-5">
          <img src="assets/img/person-1.jpg" alt="" class="img-fluid w-50 mb-4">
          <h4>Cameron Williamson</h4>
          <span class="d-block mb-3 text-uppercase">Founder &amp; CEO</span>
          <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis, perspiciatis repellat maxime, adipisci non ipsam at itaque rerum vitae, necessitatibus nulla animi expedita cumque provident inventore? Voluptatum in tempora earum deleniti, culpa odit veniam, ea reiciendis sunt ullam temporibus aut!</p>
        </div>
        @endforeach
      </div>
    </div>
</section>
@endsection
