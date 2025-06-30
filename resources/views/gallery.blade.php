@extends('layouts.user')
@push('meta_user')
<meta name="description" content="Aktivitas Gallery Sekolah Kreatif SD Muhammadiyah 3 Samarinda">
<meta name="keywords" content="Aktivitas, Sekolah Kreatif SD Muhammadiyah 3 Samarinda, Galeri, Dokumentasi">
<meta name="author" content="Sekolah Kreatif SD Muhammadiyah 3 Samarinda">
<meta name="copyright" content="Sekolah Kreatif SD Muhammadiyah 3 Samarinda">
<meta property="og:title" content="Gallery Sekolah Kreatif SD Muhammadiyah 3 Samarinda">
<meta property="og:description" content="Aktivitas Gallery Sekolah Kreatif SD Muhammadiyah 3 Samarinda">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ count($gallerys) ? (asset('storage/img/gallery/cover/' . $gallerys[0]->cover ?? '')) : asset('asset_new/images/SD3_logo1.png') }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Gallery Sekolah Kreatif SD Muhammadiyah 3 Samarinda">
<meta name="twitter:description" content="Dokumentasi kegiatan dan aktivitas siswa Sekolah Kreatif SD Muhammadiyah 3 Samarinda.">
<meta name="twitter:image" content="{{ count($gallerys) ? (asset('storage/img/gallery/cover/' . $gallerys[0]->cover ?? '')) : asset('asset_new/images/SD3_logo1.png') }}">

{{-- Schema JSON-LD for ImageGallery --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ImageGallery",
  "name": "Gallery Aktivitas Sekolah Kreatif SD Muhammadiyah 3 Samarinda",
  "url": "{{ url()->current() }}",
  "description": "Kumpulan dokumentasi foto aktivitas dan kegiatan siswa Sekolah Kreatif SD Muhammadiyah 3 Samarinda.",
  "image": [
    @foreach ($gallerys->take(6) as $gallery)
      @php
        $fotos = is_array($gallery->foto) ? $gallery->foto : explode(',', $gallery->foto);
        $firstFoto = trim($fotos[0] ?? '');
        $imgSrc = $gallery->cover
            ? asset('storage/img/gallery/cover/' . $gallery->cover)
            : asset('storage/img/gallery/' . $firstFoto);
      @endphp
      "{{ $imgSrc }}"{!! !$loop->last ? ',' : '' !!}
    @endforeach
  ],
  "publisher": {
    "@type": "Organization",
    "name": "SD Muhammadiyah 3 Samarinda",
    "logo": {
      "@type": "ImageObject",
      "url": "{{ asset('asset_new/images/SD3_logo1.png') }}"
    }
  }
}
</script>
@endpush

@section('title', 'Gallery')

@section('content')
<div class="main-banner">
    <div class="container">
        <header class="section-header text-center wow fadeInDown" data-wow-delay="0.2s">
            <h2>Gallery Aktivitas</h2>
            <h4>SD Muhammadiyah 3 Samarinda</h4>
        </header>

        <div class="form-group mt-3">
            <a href="{{ route('index') }}" class="btn btn-primary"
                style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="row mt-4">
            @forelse ($gallerys as $gallery)
                @php
                    $fotos = is_array($gallery->foto) ? $gallery->foto : explode(',', $gallery->foto);
                    $firstFoto = trim($fotos[0] ?? 'default.jpg');
                    $imgSrc = $gallery->cover
                        ? asset('storage/img/gallery/cover/' . $gallery->cover)
                        : asset('storage/img/gallery/' . $firstFoto);
                @endphp

                <div class="col-lg-4 text-center mb-5 wow fadeInUp" data-wow-delay="0.2s">
                    <a href="{{ route('gallery.show', $gallery->slug) }}">
                        <img src="{{ $imgSrc }}" alt="{{ $gallery->name }}"
                            class="img-fluid rounded w-100 mb-4">
                    </a>
                    <h5>{{ $gallery->name }}</h5>
                </div>
            @empty
                <div class="col-lg-12 text-center mb-5">
                    <h4>Tidak ada Gallery</h4>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
