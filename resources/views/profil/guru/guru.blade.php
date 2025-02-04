@extends('layouts.user')
@section('title','Guru')

@section('content')
<section class="guru" style="margin-top: 20px; margin-bottom: 0;">
    <div class="container">
       <div class="row">
        {{-- <div class="col-md-12">
            <a href="{{ route('index') }}" class="btn btn-primary float-start" style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div> --}}
        <div class="col-md-12 mt-3 wow fadeInUp" data-wow-delay="0.2s">
            <div class="section-title text-center">
                <h2>Guru SD Muhammadiyah 3 Samarinda</h2>
            </div>
        </div>

       </div>
        <hr class="wow fadeInUp" data-wow-delay="0.2s">
    </div>
</section>
<div class="container-fluid team pb-5">
    <div class="container pb-5">
        <div class="row g-4">
            @foreach ($gurus as $guru)
            <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.2s">
                <div class="team-item">
                    <div class="team-img">
                        <img src="{{ asset('storage/img/guru/'. $guru->foto) }}"  class="img-fluid rounded-top w-100" alt="">
                    </div>
                    <div class="team-title p-4">
                        <h4 class="mb-0">{{ $guru->name }}</h4>
                        <p class="mb-1">Lulusan : {{ $guru->lulusan }}</p>
                        <p class="mb-1">Mengajar :</p>
                        @forelse ($guru->pelajarans as $pelajaran)
                        <p class="mb-0">{{ $pelajaran->name }} <br></p>
                        @empty

                        @endforelse
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
