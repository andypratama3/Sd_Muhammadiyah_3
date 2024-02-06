@extends('layouts.user')
@section('title','Fasilitas')
@section('content')
<section id="fasilitas" class="fasilitas">

    <div class="container" data-aos="fade-up">
        <header class="section-header text-center">
            <h2>Fasilitas</h2>
            <h4>SD Muhammadiyah 3 Samarinda</h4>
        </header>

        <div class="row gy-4 fasilitas-container" data-aos="fade-up" data-aos-delay="200" style="margin-bottom: 20px;">
            @foreach ($fasilitas as $fas)
            <div class="col-lg-4 col-md-6 fasilitas-item" style="margin-top:40px;">
                <div class="fasilitas-wrap">
                    <img src="{{ asset('storage/img/fasilitas/'. $fas->foto) }}" class="img-fluid" alt="">
                    <div class="fasilitas-info">
                        <h4>{{ $fas->nama_fasilitas }}</h4>
                        <p>{{ $fas->desc }}</p>
                        <div class="fasilitas-links">
                            <a href="{{ asset('storage/img/fasilitas/'. $fas->foto) }}" data-gallery="fasilitasGallery"
                                class="fasilitas-lightbox" title="{{ $fas->nama_fasilitas }} || {{ $fas->desc }}"><i
                                    class="bi bi-plus"></i></a>
                            <a href="fasilitas-details.html" title="More Details"><i class="bi bi-link"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section><!-- End fasilitas Section -->
@push('js')
<script>
    const fasilitasLightbox = GLightbox({
        selector: '.fasilitas-lightbox'
    });
</script>
@endpush
@endsection
