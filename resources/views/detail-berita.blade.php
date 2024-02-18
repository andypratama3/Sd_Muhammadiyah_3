@extends('layouts.user')
@section('title','Detail')
@section('content')

<section class="single-post-content" style="margin-top: 94px;">
    <div class="container">
        <div class="row">
            <div class="col-md-9 post-content" data-aos="fade-up">
                <!-- ======= Single Post Content ======= -->
                <div class="single-post">
                    <div class="post-meta"><span class="date">Di Posting Pada</span> <span class="mx-1">&bullet;</span>
                        <span> {{ $berita->created_at }}</span></div>
                        <figure class="my-4">
                            <img src="{{ asset('storage/img/berita/'. $berita->foto) }}" alt="" class="img-fluid">
                            </figcaption>
                        </figure>
                    <h1 class="mb-2">{{ $berita->judul }}</h1>
                    <p>{{ $berita->desc }}.</p>
                </div>
            </div>
            <div class="col-md-3">
                <!-- ======= Sidebar ======= -->
                <div class="aside-block">

                    <ul class="nav nav-pills custom-tab-nav mb-4" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-trending-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-trending" type="button" role="tab" aria-controls="pills-trending"
                                aria-selected="false">Trending</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-latest-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-latest" type="button" role="tab" aria-controls="pills-latest"
                                aria-selected="false">Latest</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent">

                        <!-- Trending -->
                        <div class="tab-pane fade" id="pills-trending" role="tabpanel"
                            aria-labelledby="pills-trending-tab">
                            @foreach ($artikel_trending_list as $trending)
                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta"><span class="date">Trending</span> <span
                                    class="mx-1">&bullet;</span> <span>{{ $trending->created_at }}</span></div>
                                    <h2 class="mb-2"><a href="{{ route('artikel.show', $trending->slug) }}">{{ $trending->name }}</a></h2>
                                </div>
                            @endforeach
                        </div> <!-- End Trending -->

                        <!-- Latest -->
                        <div class="tab-pane fade" id="pills-latest" role="tabpanel" aria-labelledby="pills-latest-tab">
                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta"><span class="date">Lifestyle</span> <span
                                        class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
                                <h2 class="mb-2"><a href="#">Life Insurance And Pregnancy: A Working Mom’s Guide</a>
                                </h2>
                                <span class="author mb-3 d-block">Jenny Wilson</span>
                            </div>

                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta"><span class="date">Business</span> <span
                                        class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
                                <h2 class="mb-2"><a href="#">The Best Homemade Masks for Face (keep the Pimples
                                        Away)</a></h2>
                                <span class="author mb-3 d-block">Jenny Wilson</span>
                            </div>

                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta"><span class="date">Lifestyle</span> <span
                                        class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
                                <h2 class="mb-2"><a href="#">10 Life-Changing Hacks Every Working Mom Should Know</a>
                                </h2>
                                <span class="author mb-3 d-block">Jenny Wilson</span>
                            </div>

                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta"><span class="date">Sport</span> <span
                                        class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
                                <h2 class="mb-2"><a href="#">How to Avoid Distraction and Stay Focused During Video
                                        Calls?</a></h2>
                                <span class="author mb-3 d-block">Jenny Wilson</span>
                            </div>

                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta"><span class="date">Lifestyle</span> <span
                                        class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
                                <h2 class="mb-2"><a href="#">17 Pictures of Medium Length Hair in Layers That Will
                                        Inspire Your New Haircut</a></h2>
                                <span class="author mb-3 d-block">Jenny Wilson</span>
                            </div>

                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta"><span class="date">Culture</span> <span
                                        class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
                                <h2 class="mb-2"><a href="#">9 Half-up/half-down Hairstyles for Long and Medium Hair</a>
                                </h2>
                                <span class="author mb-3 d-block">Jenny Wilson</span>
                            </div>

                        </div> <!-- End Latest -->

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
