@foreach ($beritas as $berita)
<div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.2s">
    <div class="blog-item">
        <div class="blog-img">
            <img src="{{ asset('storage/img/berita/' . $berita->foto) }}" class="img-fluid rounded-top w-100" alt="{{ $berita->judul }}">
            <div class="blog-categiry py-2 px-4">
                <span>Berita</span>
            </div>
        </div>
        <div class="blog-content p-4">
            <div class="blog-comment d-flex justify-content-between mb-3">
                {{-- <div class="small"><span class="fa fa-user text-primary"></span> Martin.C</div> --}}
                <div class="small"><span class="fa fa-calendar text-primary"></span>
                    {{ \Carbon\Carbon::parse($berita->created_at)->diffForHumans() }}</div>
            </div>
            <a href="{{ route('berita.show', $berita->slug) }}" class="h4 d-inline-block mb-3">{{ $berita->judul }}</a>
            {{-- <p class="mb-3">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Eius libero soluta impedit eligendi? Quibusdam, laudantium.</p> --}}
            <a href="{{ route('berita.show', $berita->slug) }}" class="btn p-0">Lihat Berita <i
                    class="fa fa-arrow-right"></i></a>
        </div>
    </div>
</div>
@endforeach
