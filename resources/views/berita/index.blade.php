@extends('layouts.user')
@section('title','Berita')
@section('content')
<section class="ftco-section bg-light ftco-no-pt" style="margin-top: 100px;">
    <div class="container-xl">
        <div class="row justify-content-center mb-5">
            <div class="col-md-7 heading-section text-center aos-init aos-animate" data-aos="fade-up"
                data-aos-duration="1000">
                <h2>Berita</h2>
            </div>
        </div>
        <div class="row justify-content-center">
            @foreach ($beritas as $berita)
                <div class="col-md-6 col-lg-4 d-flex">
                    <div class="blog-entry justify-content-end aos-init aos-animate" data-aos="fade-up"
                        data-aos-duration="1000" data-aos-delay="100">
                        <a href="blog-single.html" class="block-20 img"
                            style="background-image: url({{ url('storage/img/berita/'.$berita->foto)  }})">
                        <div class="text">
                            <p class="meta"><span><i class="fa fa-user me-1"></i>Admin</span> <span><i
                                        class="fa fa-calendar me-1"></i>Jan. 18, 2021</span> <span><a href="#"><i
                                            class="fa fa-comment me-1"></i> 3 Comments</a></span></p>
                            <h3 class="heading mb-3"><a href="#">Build your Dream Software &amp; Engineering Career</a></h3>
                            <p>A small river named Duden flows by their place and supplies it with the necessary regelialia.
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@push('js_user')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endpush
@endsection
