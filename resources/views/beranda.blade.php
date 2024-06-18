@extends('layouts.user')
@section('title','Beranda')
@push('css')
    <style>
        .img-welcome{
            border-radius: 10px !important;
        }
    </style>
@endpush
@section('content')
<!-- ======= Hero Slider Section ======= -->
<section id="hero-slider" class="hero-slider" style="margin-top: 94px;">
    <div class="container-md text-center" data-aos="fade-in">
        <div class="row">
            <div class="col-12">
                <div class="swiper sliderFeaturedPosts" style="border-radius: 15px;">
                    <div class="swiper-wrapper">
                        @foreach ($beritas as $berita)
                        <div class="swiper-slide">
                            {{-- <a href="{{ route('berita.show',$berita->slug) }}" class="img-bg d-flex align-items-end"
                                style="background-image: url({{ url('storage/img/berita/'.$berita->foto)  }})"> --}}
                                <a href="{{ route('berita.show',$berita->slug) }}" class="img-bg d-flex align-items-end"
                                    style="background-image: url({{ url('storage/img/berita/Berita_test_20240412172357.jpg')  }}); border-radius: 10px;">
                                <div class="img-bg-inner">
                                    <h2>{{ $berita->judul }}</h2>
                                    <p>{{ Str::substr($berita->desc, 0, 40) }}</p>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                    <div class="custom-swiper-button-next">
                        <span class="bi-chevron-right"></span>
                    </div>
                    <div class="custom-swiper-button-prev">
                        <span class="bi-chevron-left"></span>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </div>

    </div>
</section>
<!-- End Hero Slider Section -->

<section id="about">
    <div class="container aos-init aos-animate">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <h4 class="page-title">Tentang</h4>
            </div>
        </div>

        <div class="row mb-5">

            <div class="d-md-flex post-entry-2 half">
                <div class="me-4 thumbnail">
                    <img src="{{ asset('storage/img/berita/Berita_test_20240412172357.jpg') }}" alt=""  class="img-fluid img-welcome" data-aos="fade-up-right">
                </div>
                <div class="ps-md-5 mt-4 mt-md-0" data-aos="fade-up-left">
                    <div class="post-meta mt-4">Tentang</div>
                    <h6 class="mb-4 display-6 text-center">SD Muhammadiyah 3 Samarinda</h6>

                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis, perspiciatis repellat maxime,
                        adipisci non ipsam at itaque rerum vitae, necessitatibus nulla animi expedita cumque provident
                        inventore? Voluptatum in tempora earum deleniti, culpa odit veniam, ea reiciendis sunt ullam
                        temporibus aut!</p>
                    <p>Fugit eaque illum blanditiis, quo exercitationem maiores autem laudantium unde excepturi dolores
                        quasi eos vero harum ipsa quam laborum illo aut facere voluptates aliquam adipisci sapiente
                        beatae ullam. Tempora culpa iusto illum accusantium cum hic quisquam dolor placeat officiis
                        eligendi.</p>
                </div>
            </div>

            <div class="d-md-flex post-entry-2 half mt-5">
                <a href="#" class="me-4 thumbnail order-2">
                    <img src="{{ asset('storage/img/berita/Berita_test_20240412172357.jpg') }}" alt="" class="img-fluid img-welcome" data-aos="fade-up-left">
                </a>
                <div class="pe-md-5 mt-4 mt-md-0" data-aos="fade-up-right">
                    <div class="post-meta mt-4">Visi &amp; Misi</div>
                    <h2 class="mb-4 display-6">Visi &amp; Misi</h2>

                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis, perspiciatis repellat maxime,
                        adipisci non ipsam at itaque rerum vitae, necessitatibus nulla animi expedita cumque provident
                        inventore? Voluptatum in tempora earum deleniti, culpa odit veniam, ea reiciendis sunt ullam
                        temporibus aut!</p>
                    <p>Fugit eaque illum blanditiis, quo exercitationem maiores autem laudantium unde excepturi dolores
                        quasi eos vero harum ipsa quam laborum illo aut facere voluptates aliquam adipisci sapiente
                        beatae ullam. Tempora culpa iusto illum accusantium cum hic quisquam dolor placeat officiis
                        eligendi.</p>
                </div>
            </div>

        </div>

    </div>
</section>

<section id="counts" class="counts">
    <div class="container" data-aos="fade-up">

        <div class="row gy-4">

            <div class="col-lg-3 col-md-6">
                <div class="count-box">
                    <i class="bi bi-emoji-smile"></i>
                    <div>
                        <span data-purecounter-start="0" data-purecounter-end="{{ $siswas }}" data-purecounter-duration="1"
                            class="purecounter">{{ $siswas }}</span>
                        <p>Siswa</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="count-box">
                    <i class="bi bi-journal-richtext" style="color: #ee6c20;"></i>
                    <div>
                        <span data-purecounter-start="1" data-purecounter-end="{{ $guru }}" data-purecounter-duration="1" class="purecounter">{{ $guru }}</span>
                        <p>Guru</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="count-box">
                    <i class="bi bi-headset" style="color: #15be56;"></i>
                    <div>
                        <span data-purecounter-start="0" data-purecounter-end="{{ $fasilitas }}"
                            data-purecounter-duration="1" class="purecounter">{{ $fasilitas }}</span>
                        <p>Fasilitas</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="count-box">
                    <i class="bi bi-people" style="color: #bb0852;"></i>
                    <div>
                        <span data-purecounter-start="0" data-purecounter-end="15" data-purecounter-duration="1"
                            class="purecounter">{{ $esktrakurikuler }}</span>
                        <p>Ekstrakurikuler</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- <script>
  const slider = document.querySelector(".slider");

  function activate(e) {
    const items = document.querySelectorAll(".item");
    e.target.matches(".next") && slider.append(items[0]);
    e.target.matches(".prev") && slider.prepend(items[items.length - 1]);
  }

  document.addEventListener("click", activate, false);
</script> -->
<!-- <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script> -->
<!-- <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script> -->
@push('js')
<script src="{{ asset('assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>
<script>
    // AOS.init();
    document.addEventListener("click", activate, false);
    (function () {
        "use strict";
        const select = (el, all = false) => {
            el = el.trim()
            if (all) {
                return [...document.querySelectorAll(el)]
            } else {
                return document.querySelector(el)
            }
        }
        const on = (type, el, listener, all = false) => {
            if (all) {
                select(el, all).forEach(e => e.addEventListener(type, listener))
            } else {
                select(el, all).addEventListener(type, listener)
            }
        }
        new PureCounter();
    })();
</script>
@endpush
@endsection
