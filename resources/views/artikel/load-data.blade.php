
@foreach ($artikel_not_trending as $artikel)
@if (!$artikels_trending->contains('id', $artikel->id))
<div class="col-lg-4 border-start custom-border">
    <div class="post-entry-1">
        <a href="{{ route('artikel.show', $artikel->slug) }}">
            {{-- <img src="{{ asset('storage/app/public/img/artikel/'. $artikel->image) }}" alt=""
            class="img-fluid"> --}}
            <img src="{{ asset('storage/img/berita/Berita_test_20240412172357.jpg') }}" alt=""
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

