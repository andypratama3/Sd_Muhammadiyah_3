@extends('layouts.user')
@section('title','Detail ekstrakurikuler')
@section('content')
<section>
    <div class="container aos-init aos-animate" data-aos="fade-up">
        <div class="row">
            <header class="section-header text-center">
                <h2>Ekstrakurikuler {{ $ekstrakurikuler->name }}</h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
            </header>
            @php
            $ekstrakurikuler_foto = explode(',',$ekstrakurikuler->foto);
            $firstCover = reset($ekstrakurikuler_foto);
            @endphp
            <div class="col-lg-4 mb-5" style="margin-top: 40px;">
                <div class="form-group">
                    <div class="d-flex" style="grid-template-columns: 60px 60px;">
                        @foreach ($ekstrakurikuler_foto as $image => $i)
                        <img src="{{ asset('storage/img/ekstrakurikuler/'. trim($i)) }}" alt="" srcset=""
                            style="width: 100%; height: 100%; margin: 20px;">
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
</section>
@endsection
