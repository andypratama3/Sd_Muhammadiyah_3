@extends('layouts.user')
@section('title','Artikel')
@section('content')
<section class="single-post-content">
    <div class="container">
        <div class="row">
            <div class="col-md-9 post-content" data-aos="fade-up">

                <div class="single-post">
                    <div class="post-meta"><span class="date">
                            @foreach ($artikel->categorys as $category)
                            {{ $category->name }}
                            @endforeach
                        </span>
                        <span class="mx-1">&bullet;</span>
                        <span>{{ $artikel->created_at->formatLocalized('%A %d %B %Y') }}</span></div>
                    <h1 class="mb-5">{{ $artikel->name }}</h1>
                    <figure class="my-4">
                        <img src="{{ asset('storage/app/public/img/artikel/'. $artikel->image) }}" alt=""
                            class="img-fluid">
                    </figure>
                    <p><span class="firstcharacter">{{ $firstCharacter }}</span>{{ $contentWithoutFirstCharacter }}</p>
                </div>
                <!-- ======= Comments ======= -->
                <div class="comments my-5">
                    <h5 class="comment-title py-4">{{ $count }} Comments</h5>
                    @foreach ($comments as $comment)
                    @if ($count >= 0)
                        <div class="comment d-flex mb-4">
                            @foreach ($comment->users as $user)
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-sm rounded-circle">
                                    <img class="avatar-img" src="{{ asset('storgae/app/public/img/user', $user->foto) }}" alt="" class="img-fluid">
                                </div>
                            </div>

                            <div class="flex-grow-1 ms-2 ms-sm-3">
                                <div class="comment-meta d-flex align-items-baseline">

                                    <h6 class="me-2">{{ $user->name }}</h6>
                                    <span class="text-muted">{{ $comment->created_at->diffForHumans() }}</span>

                                </div>
                                @endforeach
                                <div class="comment-body">
                                    {{ $comment->comment }}
                                </div>
                                <div class="comment-meta">
                                    <div class="comment-meta">
                                        @foreach ($comment->users as $item)
                                        @if ($item->id === Auth::user()->id)
                                        <a href="#" class="btn btn-primary btn-sm">Edit Data</a>
                                        @else
                                                {{-- <label for=""><i class="fa fas-love"></i></label> --}}
                                                <h2>Like</h2>
                                        @endif
                                        @endforeach
                                    </div>
                                </div>

                            </div>
                        </div>
                        @else
                        <h2 class="text-muted">Tidak Ada Komentar</h2>
                    @endif
                    @endforeach
                </div><!-- End Comments -->

                <!-- ======= Comments Form ======= -->
                <div class="row justify-content-center mt-5">

                    <div class="col-lg-12">
                        <h5 class="comment-title">Komentar</h5>
                        @include('layouts.flashmessage')
                        <div class="row" id="reset_komen">
                            <form id="comment-form" action="{{ route('post.comment') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="artikel" value="{{ $artikel->id }}">
                            <input type="hidden" name="user" value="{{ Auth::id() }}">
                                <div class="col-12 mb-3">
                                    <label for="comment-message">Tambahkan Komentar</label>
                                    <textarea class="form-control" name="comment" id="comment-message" placeholder="Masukan Teks" cols="30" rows="10"></textarea>
                                </div>
                                @auth
                                <div class="col-12">
                                    <input type="submit" class="btn btn-primary" value="Posting">
                                </div>
                                @else
                                <div class="col-12">
                                    <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
                                    <a href="{{ route('login') }}" class="btn btn-primary">Masuk</a>
                                </div>
                                @endauth
                            </form>
                        </div>
                    </div>
                </div><!-- End Comments Form -->
            </div>
            <div class="col-md-3">
                <!-- ======= Sidebar ======= -->
                <div class="aside-block">
                    <ul class="nav nav-pills custom-tab-nav mb-4" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-popular-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-popular" type="button" role="tab" aria-controls="pills-popular"
                                aria-selected="true">Popular</button>
                        </li>
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

                        <!-- Popular -->
                        <div class="tab-pane fade show active" id="pills-popular" role="tabpanel"
                            aria-labelledby="pills-popular-tab">
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
                        </div> <!-- End Popular -->

                        <!-- Trending -->
                        <div class="tab-pane fade" id="pills-trending" role="tabpanel" aria-labelledby="pills-trending-tab">
                            @foreach ($artikel_trending_list as $trending)
                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta">
                                    <span class="date"></span>
                                <span class="mx-1">&bullet;</span> <span>{{ $trending->created_at->formatLocalized('%A %d %B %Y') }}</span></div>
                                <h2 class="mb-2"><a href="{{ route('artikel.show', $trending->slug) }}">{{ $trending->name }}</a></h2>
                                @foreach ($trending->categorys as $category)
                                <span class="author mb-3 d-block">{{ $category->name }}</span>
                                @endforeach
                            </div>
                            @endforeach
                        </div>
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

                <div class="aside-block">
                    <h3 class="aside-title">Video</h3>
                    <div class="video-post">
                        <a href="https://www.youtube.com/watch?v=AiFfDjmd0jU" class="glightbox link-video">
                            <span class="bi-play-fill"></span>
                            <img src="assets/img/post-landscape-5.jpg" alt="" class="img-fluid">
                        </a>
                    </div>
                </div><!-- End Video -->

                <div class="aside-block">
                    <h3 class="aside-title">Categories</h3>
                    <ul class="aside-links list-unstyled">
                        <li><a href="category.html"><i class="bi bi-chevron-right"></i> Business</a></li>
                        <li><a href="category.html"><i class="bi bi-chevron-right"></i> Culture</a></li>
                        <li><a href="category.html"><i class="bi bi-chevron-right"></i> Sport</a></li>
                        <li><a href="category.html"><i class="bi bi-chevron-right"></i> Food</a></li>
                        <li><a href="category.html"><i class="bi bi-chevron-right"></i> Politics</a></li>
                        <li><a href="category.html"><i class="bi bi-chevron-right"></i> Celebrity</a></li>
                        <li><a href="category.html"><i class="bi bi-chevron-right"></i> Startups</a></li>
                        <li><a href="category.html"><i class="bi bi-chevron-right"></i> Travel</a></li>
                    </ul>
                </div><!-- End Categories -->

                <div class="aside-block">
                    <h3 class="aside-title">Tags</h3>
                    <ul class="aside-tags list-unstyled">
                        <li><a href="category.html">Business</a></li>
                        <li><a href="category.html">Culture</a></li>
                        <li><a href="category.html">Sport</a></li>
                        <li><a href="category.html">Food</a></li>
                        <li><a href="category.html">Politics</a></li>
                        <li><a href="category.html">Celebrity</a></li>
                        <li><a href="category.html">Startups</a></li>
                        <li><a href="category.html">Travel</a></li>
                    </ul>
                </div><!-- End Tags -->

            </div>
        </div>
    </div>
</section>
@push('js_user')
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/3.3.1/js.cookie.min.js"></script>
<script src="{{ asset('asset_dashboard/js/SwetAlert/index.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#comment-form').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(response) {
                swal({
                    title: response.success,
                    icon: 'success',
                });
                $('.comments').load(location.href + " .comments");
                $('#reset_komen').load(location.href + " #reset_komen");
            },
            error: function(error) {

            }
        });
    });
});
</script>
@endpush
@endsection
