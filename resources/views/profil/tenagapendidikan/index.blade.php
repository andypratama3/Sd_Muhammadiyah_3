@extends('layouts.user')
@section('title','Tenaga Kependidikan')
@push('css_user')
<style>
    .tenagakependidikan .member {
        text-align: center;
        margin-bottom: 20px;
        background: #343a40;
        position: relative;
        overflow: hidden;
    }

    .tenagakependidikan .member .member-info {
        opacity: 0;
        position: absolute;
        bottom: 0;
        top: 0;
        left: 0;
        right: 0;
        transition: 0.2s;
    }

    .tenagakependidikan .member .member-info-content {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 10px;
        transition: bottom 0.4s;
    }

    .tenagakependidikan .member .member-info-content h3 {
        font-weight: 700;
        margin-bottom: 2px;
        font-size: 24px;
        color: #fff;
    }

    .tenagakependidikan .member .member-info-content span {
        font-style: italic;
        display: block;
        font-size: 17px;
        color: #fff;
    }


    .tenagakependidikan .member .social a {
        transition: color 0.3s;
        color: #fff;
        margin: 0 10px;
        display: inline-block;
    }

    .tenagakependidikan .member .social a:hover {
        color: #cda45e;
    }

    .tenagakependidikan .member .social i {
        font-size: 18px;
        margin: 0 2px;
    }

    .tenagakependidikan .member:hover .member-info {
        background: linear-gradient(0deg, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.8) 20%, rgba(0, 212, 255, 0) 100%);
        opacity: 1;
        transition: 0.4s;
    }

    .tenagakependidikan .member:hover .member-info-content {
        bottom: 60px;
        transition: bottom 0.4s;
    }

    .tenagakependidikan .member:hover .social {
        bottom: 0;
        transition: bottom ease-in-out 0.4s;
    }
</style>
@endpush
@section('content')

<section class="tenagakependidikan" style="margin-top: 95px;">
    <div class="container aos-init aos-animate" data-aos="fade-up">
        <div class="section-title text-center">
            <h2>Tenaga Kependidikan SD Muhammadiyah 4</h2>
        </div>
        <hr>
        <div class="row">
            @foreach ($tenagakependidikans as $tenagapendidikan)
            <div class="col-lg-4 col-md-6">
                <div class="member" data-aos="zoom-in" data-aos-delay="100">
                    <a href="{{ asset('storage/img/tenagapendidikan/'. $tenagapendidikan->foto) }}" class="glightbox"
                        data-glightbox="title: {{ $tenagapendidikan->name }}; description: Jabatan Sebagai {{ $tenagapendidikan->name }}; type: image; effect: fade; width: 900px; height: auto; zoomable: true; draggable: true;">
                        <img src="{{ asset('storage/img/tenagapendidikan/'. $tenagapendidikan->foto) }}"
                            class="img-fluid" alt="">

                    <div class="member-info">
                        <div class="member-info-content">
                            <h3>{{ $tenagapendidikan->name }}</h3>
                            <span>{{ $tenagapendidikan->jabatan }}</span>
                        </div>
                    </div>
                </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
