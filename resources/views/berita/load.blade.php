@foreach ($beritas as $berita)
<div class="col-md-6 col-lg-4 d-flex mt-2 aos-init aos-animate" data-aos="fade-up"
                        data-aos-duration="1000" data-aos-delay="100" id="col-md-6">
    <div class="blog-entry justify-content-end aos-init aos-animate" data-aos="fade-up"
        data-aos-duration="1000" data-aos-delay="100">
        <a href="{{ route('berita.show', $berita->slug) }}">
            <img src="{{ asset('storage/img/berita/'. $berita->foto ) }}" alt="" class="img-fluid">
            <div class="text">
                <p class="meta mt-2"><span><i class="fa fa-user me-1"></i>Admin</span> <span><i
                            class="fa fa-calendar me-1"></i>{{ $berita->created_at->format('Y:m:d') }}</span> <span><a href="#"><i
                                class="fa fa-comment me-1"></i> 3 Comments</a></span></p>
                <h3 class="heading mb-3"><a href="{{ route('berita.show', $berita->slug) }}">{{ $berita->judul }}</a>
                </h3>
                <p>
                    {{ Str::substr($berita->desc, 0, 50). ' ......' }}
                </p>
            </div>
        </a>
    </div>
</div>
@endforeach
