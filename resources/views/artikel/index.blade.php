@extends('layouts.user')
@section('title','Artikel')

@section('content')

<section id="posts" class="posts" style="margin-top: 95px;">
    <div class="container" data-aos="fade-up">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="post-entry-1 lg">
                    @foreach ($artikels_trending as $artikel)
                    <a href="{{ route('artikel.show', $artikel->slug) }}">
                        {{-- <img src="{{ asset('storage/img/artikel/'. $artikel->image) }}" alt="" class="img-fluid">
                        --}}
                        <img src="{{ asset('storage/img/artikel/Berita_a_20240210170052.jpg') }}" alt=""
                            class="img-fluid">
                    </a>
                    <div class="post-meta"><span class="date">Di Posting</span> <span class="mx-1">&bullet;</span>
                        <span>{{ $artikel->created_at->formatLocalized('%A %d %B %Y') }}</span></div>
                    <h2><a href="{{ route('artikel.show', $artikel->slug) }}">{{ $artikel->name }}</a></h2>
                    @php
                    $content = strip_tags($artikel->artikel);
                    $secondParagraph = substr($content, strpos($content, '</p>') + 4);
                    @endphp

                    <p>{!! $secondParagraph !!}</p>
                    {{-- <p>{!! substr(strip_tags($artikel->artikel), 0, strpos(strip_tags($artikel->artikel), '</p>') + 4) !!}</p> --}}

                    <div class="d-flex align-items-center author">
                        <div class="name mt-4">
                            <h3 class="author">Kategori : </h3>
                            @foreach ($artikel->categorys as $category)
                            <span class="author">{{ $category->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row g-5">
                    @foreach ($artikel_not_trending as $artikel)
                    @if (!$artikels_trending->contains('id', $artikel->id))
                    <div class="col-lg-4 border-start custom-border">
                        <div class="post-entry-1">
                            <a href="{{ route('artikel.show', $artikel->slug) }}">
                                {{-- <img src="{{ asset('storage/app/public/img/artikel/'. $artikel->image) }}" alt=""
                                class="img-fluid"> --}}
                                <img src="{{ asset('storage/img/artikel/Berita_a_20240210170052.jpg') }}" alt=""
                                    class="img-fluid">
                            </a>
                            <div class="post-meta"><span class="date">Di Posting</span>
                                <span class="mx-1">&bullet;</span>
                                <span class="mt-2">{{ $artikel->created_at->formatLocalized('%A %d %B %Y') }}</span>
                            </div>
                            <h2><a href="{{ route('artikel.show', $artikel->slug) }}">{{ $artikel->name }}</a></h2>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            <!-- Trending Section -->
            <div class="col-md-2">
                <!-- ======= Sidebar ======= -->
                <div class="aside-block">
                    <ul class="nav nav-pills custom-tab-nav mb-4" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-latest-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-latest" type="button" role="tab" aria-controls="pills-latest"
                                aria-selected="true">Latest</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-trending-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-trending" type="button" role="tab" aria-controls="pills-trending"
                                aria-selected="false">Trending</button>
                        </li>

                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-latest" role="tabpanel"
                            aria-labelledby="pills-latest-tab">
                            @foreach ($latest_artikel as $latest)
                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta">
                                    <span class="date"></span>
                                    <span class="mx-1">&bullet;</span>
                                    <span>{{ $latest->created_at->formatLocalized('%A %d %B %Y') }}</span></div>
                                <h2 class="mb-2"><a
                                        href="{{ route('artikel.show', $latest->slug) }}">{{ $latest->name }}</a></h2>
                                @foreach ($latest->categorys as $category)
                                <span class="author mb-3 d-block">{{ $category->name }}</span>
                                @endforeach
                            </div>
                            @endforeach
                        </div>

                        <!-- Trending -->
                        <div class="tab-pane fade" id="pills-trending" role="tabpanel"
                            aria-labelledby="pills-trending-tab">
                            @foreach ($artikel_trending_list as $trending)
                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta">
                                    <span class="date"></span>
                                    <span class="mx-1">&bullet;</span>
                                    <span>{{ $trending->created_at->formatLocalized('%A %d %B %Y') }}</span></div>
                                <h2 class="mb-2"><a
                                        href="{{ route('artikel.show', $trending->slug) }}">{{ $trending->name }}</a>
                                </h2>
                                @foreach ($trending->categorys as $category)
                                <span class="author mb-3 d-block">{{ $category->name }}</span>
                                @endforeach
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- End .row -->
    </div>
</section> <!-- End Post Grid Section -->
@push('js_user')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        var page = 1;
        var url = "{{ route('artikel.index') }}";

        $('#load-more').click(function () {
            page++;
            $.ajax({
                url: url + '?page=' + page,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.data.length > 0) {
                        $.each(response.data, function (index, artikel) {
                            // Append the new artikel data to your HTML
                        });
                    } else {
                        $('#load-more').hide();
                    }
                }
            });
        });
    });
</script>


@endpush
@endsection
