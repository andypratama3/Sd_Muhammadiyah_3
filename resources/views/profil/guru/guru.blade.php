@extends('layouts.user_new')
@section('title','Guru')

@section('content')
<section class="guru" style="margin-top: 95px; margin-bottom: 0;">
    <div class="container aos-init aos-animate" data-aos="fade-up">
        <div class="section-title text-center">
            <h2>Guru SD Muhammadiyah 3</h2>
        </div>
    <hr>
    </div>
</section>
<div class="team section" id="team" style="margin-top: 0;">
    <div class="container">
        <div class="row">
            @foreach ($gurus as $guru)
            <div class="col-lg-3 col-md-6">
                <div class="team-member">
                    <div class="main-content">
                        <img src="{{ asset('storage/img/guru/'. $guru->foto) }}" alt="" class="img-fluid">
                        @foreach ($guru->pelajarans as $pelajaran)
                         <span class="category">{{ $pelajaran->name }}</span>
                        @endforeach
                        <h4 class="mt-4">{{ $guru->name }}</h4>
                        <p>Lulusan {{ $guru->lulusan }}</p>
                        <p>{{ $guru->description }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
