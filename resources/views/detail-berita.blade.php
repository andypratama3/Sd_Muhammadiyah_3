@extends('layouts.user')
@section('title','Detail')
@section('content'))
<section class="single-post-content">
    <div class="container">
      <div class="row">
        <div class="col-md-9 post-content" data-aos="fade-up">
          <!-- ======= Single Post Content ======= -->
          <div class="single-post">
            <div class="post-meta"><span class="date">DiPosting Pada &bullet;</span> <span>{{ date($berita->created_at) }}</span></div>
            <h1 class="mb-5">{{ $berita->judul }}</h1>
            <div class="image-detail">
                <img src="{{ asset('storage/img/berita/'.$berita->foto) }}" alt="" srcset="">
            </div>
            <div class="description">
                <p>{{ $berita->desc}}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
</section>
@endsection
