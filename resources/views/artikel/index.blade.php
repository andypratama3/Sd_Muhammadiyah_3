@extends('layouts.user')
@section('title','Artikel')

@section('content')

<section id="posts" class="posts">
    <div class="container" data-aos="fade-up">
      <div class="row g-5">
        @foreach ($artikels_trending as $artikel)
        <div class="col-lg-4">
            <div class="post-entry-1 lg">
                <a href="{{ route('artikel.show', $artikel->slug) }}"><img
                        src="{{ asset('storage/img/artikel/'. $artikel->image) }}" alt="" class="img-fluid"></a>
                <div class="post-meta"><span class="date">Di Posting</span> <span class="mx-1">&bullet;</span>
                    <span>{{ $artikel->created_at->formatLocalized('%A %d %B %Y') }}</span></div>
                <h2><a href="{{ route('artikel.show', $artikel->slug) }}">{{ $artikel->name }}</a></h2>
                {!! substr(strip_tags($artikel->artikel), 0, strpos(strip_tags($artikel->artikel), '</p>') + 4) !!}

                <div class="d-flex align-items-center author">
                    <div class="photo"><img src="assets/img/person-1.jpg" alt="" class="img-fluid"></div>
                    <div class="name mt-4">
                        <h3 class="author">Kategori : </h3>
                        @foreach ($artikel->categorys as $category)
                        <span class="author">{{ $category->name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        <div class="col-lg-6">
          <div class="row g-5">
              @foreach ($artikel_not_trending as $artikel)
              @if (!$artikels_trending->contains('id', $artikel->id))
              <div class="col-lg-4 border-start custom-border">
                    <div class="post-entry-1">
                        <a href="{{ route('artikel.show', $artikel->slug) }}">
                            <img src="{{ asset('storage/img/artikel/'. $artikel->image) }}" alt="" class="img-fluid">
                        </a>
                        <div class="post-meta"><span class="date">Di Posting</span> <span class="mx-1">&bullet;</span>
                            <span class="mt-2">{{ $artikel->created_at->formatLocalized('%A %d %B %Y') }}</span></div>
                        <h2><a href="{{ route('artikel.show', $artikel->slug) }}">{{ $artikel->name }}</a></h2>
                    </div>
                </div>
            @endif
            @endforeach
          </div>
        </div>
        <!-- Trending Section -->
        <div class="col-lg-2">
            <div class="trending">
              <h3>Trending</h3>
              <ul class="trending-post">
                  @foreach ($artikel_trending_list as $item)
                <li>
                  <a href="{{ route('artikel.show', $artikel->slug) }}">
                      <span class="number">{{ ++$no }}</span>
                      <h3>{{ $item->name }}</h3>
                      <span class="author">
                          @foreach ($item->categorys as $category)
                          {{ $category->name }}
                          @endforeach
                      </span>
                  </a>
              </li>
              @endforeach
              </ul>
            </div>
          </div>
      </div> <!-- End .row -->
    </div>
  </section> <!-- End Post Grid Section -->

@endsection