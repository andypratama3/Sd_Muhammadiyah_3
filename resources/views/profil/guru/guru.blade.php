@extends('layouts.user')
@section('title','Guru')
@push('css_user')
{{-- <style>
    .guru .member {
        text-align: center;
        margin-bottom: 20px;
        background: #343a40;
        position: relative;
        overflow: hidden;
        border-radius: 20px;
    }

    .guru .member .member-info {
        opacity: 0;
        position: absolute;
        bottom: 0;
        top: 0;
        left: 0;
        right: 0;
        transition: 0.2s;
    }

    .guru .member .member-info-content {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 10px;
        transition: bottom 0.4s;
    }

    .guru .member .member-info-content h3 {
        font-weight: 700;
        margin-bottom: 2px;
        font-size: 24px;
        color: #fff;
    }

    .guru .member .member-info-content span {
        font-style: italic;
        display: block;
        font-size: 17px;
        color: #fff;
    }


    .guru .member .social a {
        transition: color 0.3s;
        color: #fff;
        margin: 0 10px;
        display: inline-block;
    }

    .guru .member .social a:hover {
        color: #cda45e;
    }

    .guru .member .social i {
        font-size: 18px;
        margin: 0 2px;
    }

    .guru .member:hover .member-info {
        background: linear-gradient(0deg, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.8) 20%, rgba(0, 212, 255, 0) 100%);
        opacity: 1;
        transition: 0.4s;
    }

    .guru .member:hover .member-info-content {
        bottom: 60px;
        transition: bottom 0.4s;
    }

    .guru .member:hover .social {
        bottom: 0;
        transition: bottom ease-in-out 0.4s;
    }
</style> --}}
<style>
    .content-guru {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        margin-top: 10px;
        width: max-content !important;
    }
    .content-guru .image-content {
        margin: 0px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .content-guru img {
        margin-top: 20px;
        /* width: 80%; */
        height: 100vh;
        max-width: 75%;
        border-radius: 20px;
        width: fit-content;
    }
    .content-guru .img-guru i  {
        margin-top: 20px;
        font-size: 100px;
    }
    .content-guru .img-guru .icon-right {
        margin-left: 50px;
    }
    .content-guru .img-guru .icon-left {
        margin-right: 50px;
    }
    .content-guru .description-guru h2 {
        font-size: 45px;
        font-family: "Verdana, Geneva, Tahoma, sans-serif";
        font-weight: bold;
    }
    .content-guru .description-guru {
        padding-top: 150px;
        justify-content: center;
        font-size: 25px;
        align-items: center;
    }
    .content-guru .description-guru .desc-right {
        margin-right: 20px;
    }
    .content-guru .description-guru .desc-left {
        margin-left: 20px;
    }

</style>
@endpush
@section('content')

<section class="guru" style="margin-top: 95px;">
    <div class="container aos-init aos-animate" data-aos="fade-up">
        <div class="section-title text-center">
            <h2>Guru SD Muhammadiyah 4</h2>
        </div>
        <hr>
        {{-- <div class="row">
            @foreach ($gurus as $guru)
            <div class="col-lg-4 col-md-6">
                <div class="member" data-aos="zoom-out-up" data-aos-delay="100">

                    <a href="{{ asset('storage/img/guru/'. $guru->foto) }}" class="glightbox"

                        data-glightbox="title: {{ $guru->name }}; description: Lulusan Dari {{ $guru->lulusan }}; type: image; effect: fade; width: 900px; height: auto; zoomable: true; draggable: true;">
                        <img src="{{ asset('storage/img/guru/'. $guru->foto) }}" class="img-fluid" alt="">

                    <div class="member-info">
                        <div class="member-info-content">
                            <h3>{{ $guru->name }}</h3>
                            <span>{{ $guru->lulusan }}</span>
                            <label for="" style="color: #dd0b0b;">Pelajaran</label>
                            @foreach ($guru->pelajarans as $pelajaran)
                            <span>{{ $pelajaran->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    </a>
                </div>
            </div>
            @endforeach

        </div> --}}
        <div class="row">
            @foreach ($gurus as $key => $guru)
                @if($guru->count == 1)
                    <div class="content-guru" id="first_div">
                        <div class="col-md-8 image-content">
                            <div class="img-guru" data-aos="zoom-out-right" data-aos-delay="100">
                                <img src="{{ asset('storage/img/guru/'. $guru->foto) }}"/>
                                <i class="bi bi-chevron-double-right icon-right"></i>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="description-guru ">
                                <div class="desc-right" data-aos="zoom-out-up" data-aos-delay="100">
                                    <h2>{{ $guru->name }}</h2>
                                    <p>{{ $guru->description }}</p>
                                    <p>Guru :
                                        @foreach ($guru->pelajarans as $pelajaran)
                                            <p>{{ $pelajaran->name }}</p>
                                        @endforeach
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="content-guru" id="second_div">
                        <div class="col-md-4">
                            <div class="description-guru" data-aos="zoom-out-up" data-aos-delay="100">
                                <div class="desc-left">
                                    <h2>Superadmin</h2>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ex harum minima soluta explicabo voluptatum quod quisquam culpa possimus delectus magni recusandae quibusdam deleniti commodi, nisi facere vel? Quos, blanditiis officia!</p>
                                    <p>Guru : Matematika</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 image-content">
                            <div class="img-guru" data-aos="zoom-out-left" data-aos-delay="100">
                                <i class="bi bi-chevron-double-left icon-left"></i>
                                <img src="{{ asset('storage/img/guru/point.jpg') }}"/>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

    </div>
</section>

@endsection

