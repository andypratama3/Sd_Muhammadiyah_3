@foreach ($artikels_trending as $artikel)
<div class="col-lg-4 col-md-6">
    <div class="service-item">
        <div class="main-content">
            <img src="{{ asset('storage/img/artikel/'. $artikel->image) }}" alt="foto-artikel" class="img-fluid">
            <h4>{{ $artikel->name }}</h4>
            <p class="category"> Kategori :
                @foreach ($artikel->categorys as $item)
                {{ $item->name }}
                @endforeach
            </p>
            <p>{!! $artikel->artikel !!}</p>
            <div class="main-button">
                <a href="{{ route('artikel.show', $artikel->slug) }}">Lihat Artikel</a>
            </div>
        </div>
    </div>
</div>
@endforeach
</div>
