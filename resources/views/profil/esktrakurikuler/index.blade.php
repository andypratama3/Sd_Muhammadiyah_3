@extends('layouts.user')
@section('title','Ekstrakurikuler')
@push('meta_user')
    <meta name="description" content="Ekstrakurikuler menarik dari Sekolah Kreatif Muhammadiyah 3. Temukan berbagai kegiatan yang mengembangkan bakat dan minat siswa.">
    <meta name="keywords" content="Ekstrakurikuler, Sekolah Kreatif Muhammadiyah 3, Kegiatan Siswa, Pengembangan Bakat, Minat Siswa">
    <meta name="author" content="Sekolah Kreatif Muhammadiyah 3 Samarinda, Indonesia, Sekolah, Kreatif, Muhammadiyah, Samarinda, Pendidikan">



    <!-- Open Graph -->
    <meta property="og:title" content="Ekstrakurikuler - Sekolah Kreatif Muhammadiyah 3">
    <meta property="og:description" content="Jelajahi kegiatan ekstrakurikuler yang menginspirasi dan menambah wawasan siswa di Sekolah Kreatif Muhammadiyah 3.">
    <meta property="og:image" content="{{ asset('asset_new/images/SD3_logo1.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Ekstrakurikuler - Sekolah Kreatif Muhammadiyah 3">
    <meta name="twitter:description" content="Ikuti kegiatan ekstrakurikuler yang mengasah kreativitas dan kemampuan siswa di Sekolah Kreatif Muhammadiyah 3.">
    <meta name="twitter:image" content="{{ asset('asset_new/images/SD3_logo1.png') }}">
@endpush
@section('content')
<section>
    <div class="container aos-init aos-animate" style="margin-top: 20px;" data-aos="fade-up">
        <div class="row">
            <div class="mt-3 col-md-12 wow fadeInLeft" data-wow-delay="0.2s">
                <a href="{{ route('index') }}" class="btn btn-primary float-start btn-sm" style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important"><i class="fas fa-arrow-left"></i>Kembali</a>
            </div>
            <div class="mt-3 col-md-12 wow fadeInDown" data-wow-delay="0.2s">
                <header class="text-center section-header">
                    <h2>Ekstrakurikuler</h2>
                    <h4>SD Muhammadiyah 3 Samarinda</h4>
                </header>
            </div>
            @forelse ($ekstrakurikulers as $ekstrakurikuler)
            @php
                $coverArray = explode(',', rtrim($ekstrakurikuler->foto, ','));
                $firstCover = reset($coverArray);
            @endphp
            <div class="mb-5 text-center col-lg-4 wow fadeInUp" data-wow-delay="0.2s" style="margin-top: 40px;">
                <img src="{{ asset('storage/img/ekstrakurikuler/'. $firstCover )}}" alt="" class="mb-4 rounded img-fluid w-100">
                <h5>{{ $ekstrakurikuler->name }}</h5>
                <span class="mb-3 d-block text-uppercase">{{ $ekstrakurikuler->lulusan }}</span>
                <p>{{ $ekstrakurikuler->desc }}</p>

                <div class="form-group">
                    {{-- <a href="{{ route('esktrakurikuler.show', $ekstrakurikuler->name) }}" class="btn btn-primary">Lihat</a> --}}
                </div>
            </div>
            @empty
            <div class="mb-5 text-center col-lg-12 wow fadeInUp" data-wow-delay="0.2s" style="margin-top: 40px;" data-aos-delay="50">
                <h4>Tidak ada Ekstrakurikuler</h4>
            </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
