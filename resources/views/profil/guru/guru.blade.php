@extends('layouts.user')
@section('title','Guru')
@push('css_user')
<style>
.guru .member {
  text-align: center;
  margin-bottom: 20px;
  background: #343a40;
  position: relative;
  overflow: hidden;
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
  font-size: 13px;
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
</style>
@endpush
@section('content')

<section class="guru">
    <div class="container aos-init aos-animate" data-aos="fade-up">
        <div class="section-title text-center">
            <h2>Guru SD Muhammadiyah 4</h2>
        </div>
        <hr>
        <div class="row">
            @foreach ($gurus as $guru)
            <div class="col-lg-4 col-md-6">
                <div class="member" data-aos="zoom-in" data-aos-delay="100">
                    <img src="{{ asset('storage/img/guru/'. $guru->foto) }}" class="img-fluid" alt="">
                    <div class="member-info">
                        <div class="member-info-content">
                            <h3>{{ $guru->name }}</h3>
                            @foreach ($guru->pelajarans as $pelajaran)
                            <span>{{ $pelajaran->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
