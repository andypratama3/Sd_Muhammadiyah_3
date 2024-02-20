@extends('layouts.user')
@section('title','Tenaga Pendidikan')
@section('content')

<section>
    <div class="container aos-init aos-animate" data-aos="fade-up" style="margin-top: 95px;">
        <div class="row">
            <header class="section-header text-center">
                <h2>Tenaga Kependidikan</h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
            </header>
            @foreach ($datas as $tenagapendidikan)
            <div class="col-lg-4 text-center mb-5" style="margin-top: 40px;">
                <img src="{{ asset('storage/img/tenagapendidikan/'. $tenagapendidikan->foto) }}" alt="" class="img-fluid rounded w-50 mb-4">
                <h4>{{ $tenagapendidikan->name }}</h4>
                {{-- <span class="d-block mb-3 text-uppercase">{{ $tenagapendidikan-> }}</span> --}}
                <p>{{ $tenagapendidikan->jabatan }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
