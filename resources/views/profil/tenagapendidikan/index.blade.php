@extends('layouts.user')
@section('title','Tenaga Kependidikan')

@section('content')
<section class="guru" style="margin-top: 20px; margin-bottom: 0;">
    <div class="container aos-init aos-animate" data-aos="fade-up">
        <div class="section-title text-center">
            <h2>Tenaga Pendidikan SD Muhammadiyah 3</h2>
        </div>
    <hr>
    </div>
</section>
<div class="team section" id="team" style="margin-top: 0;">
    <div class="container">
        <div class="row">
            @foreach ($tenagakependidikans as $tenagapendidikan)
            <div class="col-lg-3 col-md-6">
                <div class="team-member">
                    <div class="main-content">
                        <img src="{{ asset('storage/img/guru/'. $tenagapendidikan->foto) }}" alt="" class="img-fluid">
                        <h4 class="mt-4">{{ $tenagapendidikan->name }}</h4>
                        <p>Jabatan {{ $tenagapendidikan->jabatan }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
