@foreach ($artikels_trending as $artikel)
<div class="col-lg-4 col-md-6">
    <div class="service-item">
        <div class="main-content">
            <img src="{{ asset('storage/img/artikel/'. $artikel->image) }}" alt="{{ $artikel->name }}" class="img-fluid">
            <h4>{{ $artikel->name }}</h4>
            <p class="category text-dark"> Kategori :
                @foreach ($artikel->categorys as $item)
                        {{ $item->name }}
                @endforeach
            </p>
            <p class="text-dark"> {!! Str::substr( $artikel->artikel, 0, 200) !!}</p>
            <div class="main-button">
                <a href="{{ route('artikel.show', $artikel->slug) }}" class="btn btn-primary">Lihat Artikel <i class="fa fa-angle-right"></i></a>
            </div>
        </div>
    </div>
</div>
@endforeach
</div>
