@foreach ($beritas as $berita)
    <div class="col-lg-4 col-md-6 align-self-center mb-30 event_outer col-md-6 design">
        <div class="events_item">
            <div class="thumb">
                <a href="{{ route('berita.show', $berita->slug) }}">
                    <img src="{{ asset('storage/img/berita/' . $berita->foto) }}" alt="" loading="lazy">
                    <span class="price">
                        <h6><em><code>*</code></em>New</h6>
                    </span>
            </div>
            <div class="down-content">
                <span class="author">{{ \Carbon\Carbon::parse($berita->created_at)->diffForHumans() }}</span>
                <h4>{{ $berita->judul }}</h4>
                <p>{{ Str::substr($berita->desc, 0, 50) . ' ......' }}</p>
            </div>
            </a>
        </div>
    </div>
@endforeach
