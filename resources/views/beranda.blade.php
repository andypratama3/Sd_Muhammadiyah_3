@extends('layouts.user')
@section('title','Beranda')


@section('content')
<!-- ======= Hero Slider Section ======= -->
<!-- <style>
  .center-custom{
    height: 100vh;
    display: grid;
    place-items: center;
  }
    main {
    position: relative;
    width: 100%;
    height: 100%;
  }

  .item {
    width: 200px;
    height: 200px;
    list-style-type: none;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1;
    background-position: center;
    background-size: cover;
    border-radius: 20px;
    box-shadow: 0 20px 30px rgba(255, 255, 255, 0.3) inset;
    transition: transform 0.1s, left 0.75s, top 0.75s, width 0.75s, height 0.75s;

    &:nth-child(1),
    &:nth-child(2) {
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      transform: none;
      border-radius: 0;
      box-shadow: none;
      opacity: 1;
    }

    &:nth-child(3) {
      left: 50%;
    }
    &:nth-child(4) {
      left: calc(50% + 220px);
    }
    &:nth-child(5) {
      left: calc(50% + 440px);
    }
    &:nth-child(6) {
      left: calc(50% + 660px);
      opacity: 0;
    }
  }

  .content {
    width: min(30vw, 400px);
    position: absolute;
    top: 50%;
    left: 3rem;
    transform: translateY(-50%);
    font: 400 0.85rem helvetica, sans-serif;
    color: white;
    text-shadow: 0 3px 8px rgba(0, 0, 0, 0.5);
    opacity: 0;
    display: none;

    & .title {
      font-family: "arial-black";
      text-transform: uppercase;
    }

    & .description {
      line-height: 1.7;
      margin: 1rem 0 1.5rem;
      font-size: 0.8rem;
    }

    & button {
      width: fit-content;
      background-color: rgba(0, 0, 0, 0.1);
      color: white;
      border: 2px solid white;
      border-radius: 0.25rem;
      padding: 0.75rem;
      cursor: pointer;
    }
  }

  .item:nth-of-type(2) .content {
    display: block;
    animation: show 0.75s ease-in-out 0.3s forwards;
  }

  @keyframes show {
    0% {
      filter: blur(5px);
      transform: translateY(calc(-50% + 75px));
    }
    100% {
      opacity: 1;
      filter: blur(0);
    }
  }

  .nav {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 5;
    user-select: none;

    & .btn {
      background-color: rgba(255, 255, 255, 0.5);
      color: rgba(0, 0, 0, 0.7);
      border: 2px solid rgba(0, 0, 0, 0.6);
      margin: 0 0.25rem;
      padding: 0.75rem;
      border-radius: 50%;
      cursor: pointer;

      &:hover {
        background-color: rgba(255, 255, 255, 0.3);
      }
    }
  }

  @media (width > 650px) and (width < 900px) {
    .content {
      & .title {
        font-size: 1rem;
      }
      & .description {
        font-size: 0.7rem;
      }
      & button {
        font-size: 0.7rem;
      }
    }
    .item {
      width: 160px;
      height: 270px;

      &:nth-child(3) {
        left: 50%;
      }
      &:nth-child(4) {
        left: calc(50% + 170px);
      }
      &:nth-child(5) {
        left: calc(50% + 340px);
      }
      &:nth-child(6) {
        left: calc(50% + 510px);
        opacity: 0;
      }
    }
  }

  @media (width < 650px) {
    .content {
      & .title {
        font-size: 0.9rem;
      }
      & .description {
        font-size: 0.65rem;
      }
      & button {
        font-size: 0.7rem;
      }
    }
    .item {
      width: 130px;
      height: 220px;

      &:nth-child(3) {
        left: 50%;
      }
      &:nth-child(4) {
        left: calc(50% + 140px);
      }
      &:nth-child(5) {
        left: calc(50% + 280px);
      }
      &:nth-child(6) {
        left: calc(50% + 420px);
        opacity: 0;
      }
    }
  }
</style> -->

<section id="hero-slider" class="hero-slider">
    <div class="container-md" data-aos="fade-in">
        <div class="row">
            <div class="col-12">
            <!-- <div class="center-custom">
              <main>
                <ul class="slider">
                  <li class="item" style="background-image: url('https://cdn.mos.cms.futurecdn.net/dP3N4qnEZ4tCTCLq59iysd.jpg');">
                    <div class="content">
                      <h2 class="title">"Lossless Youths"</h2>
                      <p class="description">
                        Lorem ipsum, dolor sit amet consectetur adipisicing elit. Tempore
                        fuga voluptatum, iure corporis inventore praesentium nisi. Id
                        laboriosam ipsam enim.
                      </p>
                      <button>Read More</button>
                    </div>
                  </li>
                  <li
                    class="item"
                    style="background-image: url('https://i.redd.it/tc0aqpv92pn21.jpg')"
                  >
                    <div class="content">
                      <h2 class="title">"Estrange Bond"</h2>
                      <p class="description">
                        Lorem ipsum, dolor sit amet consectetur adipisicing elit. Tempore
                        fuga voluptatum, iure corporis inventore praesentium nisi. Id
                        laboriosam ipsam enim.
                      </p>
                      <button>Read More</button>
                    </div>
                  </li>
                  <li
                    class="item"
                    style="
                      background-image: url('https://wharferj.files.wordpress.com/2015/11/bio_north.jpg');
                    "
                  >
                    <div class="content">
                      <h2 class="title">"The Gate Keeper"</h2>
                      <p class="description">
                        Lorem ipsum, dolor sit amet consectetur adipisicing elit. Tempore
                        fuga voluptatum, iure corporis inventore praesentium nisi. Id
                        laboriosam ipsam enim.
                      </p>
                      <button>Read More</button>
                    </div>
                  </li>
                  <li
                    class="item"
                    style="
                      background-image: url('https://images7.alphacoders.com/878/878663.jpg');
                    "
                  >
                    <div class="content">
                      <h2 class="title">"Last Trace Of Us"</h2>
                      <p class="description">
                        Lorem ipsum, dolor sit amet consectetur adipisicing elit. Tempore
                        fuga voluptatum, iure corporis inventore praesentium nisi. Id
                        laboriosam ipsam enim.
                      </p>
                      <button>Read More</button>
                    </div>
                  </li>
                  <li
                    class="item"
                    style="
                      background-image: url('https://theawesomer.com/photos/2017/07/simon_stalenhag_the_electric_state_6.jpg');
                    "
                  >
                    <div class="content">
                      <h2 class="title">"Urban Decay"</h2>
                      <p class="description">
                        Lorem ipsum, dolor sit amet consectetur adipisicing elit. Tempore
                        fuga voluptatum, iure corporis inventore praesentium nisi. Id
                        laboriosam ipsam enim.
                      </p>
                      <button>Read More</button>
                    </div>
                  </li>
                  <li
                    class="item"
                    style="
                      background-image: url('https://da.se/app/uploads/2015/09/simon-december1994.jpg');
                    "
                  >
                    <div class="content">
                      <h2 class="title">"The Migration"</h2>
                      <p class="description">
                        Lorem ipsum, dolor sit amet consectetur adipisicing elit. Tempore
                        fuga voluptatum, iure corporis inventore praesentium nisi. Id
                        laboriosam ipsam enim.
                      </p>
                      <button>Read More</button>
                    </div>
                  </li>
                </ul>
                <nav class="nav">
                  <ion-icon class="btn prev" name="arrow-back-outline"></ion-icon>
                  <ion-icon class="btn next" name="arrow-forward-outline"></ion-icon>
                </nav>
              </main>
            </div> -->
                <div class="swiper sliderFeaturedPosts">
                    <div class="swiper-wrapper">
                        @foreach ($beritas as $berita)
                        <div class="swiper-slide">
                            <a href="{{ route('berita.show',$berita->slug) }}" class="img-bg d-flex align-items-end"
                                style="background-image: url({{ url('storage/img/berita/'.$berita->foto)  }})">
                                <div class="img-bg-inner">
                                    <h2>{{ $berita->judul }}</h2>
                                    <p>{{ $berita->desc }}</p>
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
    <div class="container aos-init aos-animate" data-aos="fade-up">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <h4 class="page-title">Tentang</h4>
            </div>
        </div>

        <div class="row mb-5">

            <div class="d-md-flex post-entry-2 half">
                <a href="#" class="me-4 thumbnail">
                    <img src="assets/img/post-landscape-2.jpg" alt="" class="img-fluid">
                </a>
                <div class="ps-md-5 mt-4 mt-md-0">
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
                    <img src="assets/img/post-landscape-1.jpg" alt="" class="img-fluid">
                </a>
                <div class="pe-md-5 mt-4 mt-md-0">
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
                        <span data-purecounter-start="0" data-purecounter-end="100" data-purecounter-duration="1"
                            class="purecounter"></span>
                        <p>Siswa</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="count-box">
                    <i class="bi bi-journal-richtext" style="color: #ee6c20;"></i>
                    <div>
                        <span data-purecounter-start="1" data-purecounter-end="{{ $guru }}" data-purecounter-duration="1"
                            class="purecounter"></span>
                        <p>Guru</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="count-box">
                    <i class="bi bi-headset" style="color: #15be56;"></i>
                    <div>
                        <span data-purecounter-start="0" data-purecounter-end="{{ $fasilitas }}" data-purecounter-duration="1"
                            class="purecounter"></span>
                        <p>Fasilitas</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="count-box">
                    <i class="bi bi-people" style="color: #bb0852;"></i>
                    <div>
                        <span data-purecounter-start="0" data-purecounter-end="15" data-purecounter-duration="1"
                            class="purecounter"></span>
                        <p>Ekstrakurikuler</p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>
<!-- ======= Post Grid Section ======= -->
<section id="posts" class="posts">
  <div class="container" data-aos="fade-up">
    <div class="row g-5">
      <div class="col-lg-4">
        <div class="post-entry-1 lg">
          <a href="single-post.html"><img src="assets/img/post-landscape-1.jpg" alt="" class="img-fluid"></a>
          <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2><a href="single-post.html">11 Work From Home Part-Time Jobs You Can Do Now</a></h2>
          <p class="mb-4 d-block">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Vero temporibus repudiandae, inventore pariatur numquam cumque possimus exercitationem? Nihil tempore odit ab minus eveniet praesentium, similique blanditiis molestiae ut saepe perspiciatis officia nemo, eos quae cumque. Accusamus fugiat architecto rerum animi atque eveniet, quo, praesentium dignissimos</p>

          <div class="d-flex align-items-center author">
            <div class="photo"><img src="assets/img/person-1.jpg" alt="" class="img-fluid"></div>
            <div class="name">
              <h3 class="m-0 p-0">Cameron Williamson</h3>
            </div>
          </div>
        </div>

      </div>

      <div class="col-lg-8">
        <div class="row g-5">
          <div class="col-lg-4 border-start custom-border">
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-2.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Sport</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2><a href="single-post.html">Let’s Get Back to Work, New York</a></h2>
            </div>
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-5.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Food</span> <span class="mx-1">&bullet;</span> <span>Jul 17th '22</span></div>
              <h2><a href="single-post.html">How to Avoid Distraction and Stay Focused During Video Calls?</a></h2>
            </div>
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-7.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Design</span> <span class="mx-1">&bullet;</span> <span>Mar 15th '22</span></div>
              <h2><a href="single-post.html">Why Craigslist Tampa Is One of The Most Interesting Places On the Web?</a></h2>
            </div>
          </div>
          <div class="col-lg-4 border-start custom-border">
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-3.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Business</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2><a href="single-post.html">6 Easy Steps To Create Your Own Cute Merch For Instagram</a></h2>
            </div>
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-6.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Tech</span> <span class="mx-1">&bullet;</span> <span>Mar 1st '22</span></div>
              <h2><a href="single-post.html">10 Life-Changing Hacks Every Working Mom Should Know</a></h2>
            </div>
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-8.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Travel</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2><a href="single-post.html">5 Great Startup Tips for Female Founders</a></h2>
            </div>
          </div>

          <!-- Trending Section -->
          <div class="col-lg-4">

            <div class="trending">
              <h3>Trending</h3>
              <ul class="trending-post">
                <li>
                  <a href="single-post.html">
                    <span class="number">1</span>
                    <h3>The Best Homemade Masks for Face (keep the Pimples Away)</h3>
                    <span class="author">Jane Cooper</span>
                  </a>
                </li>
                <li>
                  <a href="single-post.html">
                    <span class="number">2</span>
                    <h3>17 Pictures of Medium Length Hair in Layers That Will Inspire Your New Haircut</h3>
                    <span class="author">Wade Warren</span>
                  </a>
                </li>
                <li>
                  <a href="single-post.html">
                    <span class="number">3</span>
                    <h3>13 Amazing Poems from Shel Silverstein with Valuable Life Lessons</h3>
                    <span class="author">Esther Howard</span>
                  </a>
                </li>
                <li>
                  <a href="single-post.html">
                    <span class="number">4</span>
                    <h3>9 Half-up/half-down Hairstyles for Long and Medium Hair</h3>
                    <span class="author">Cameron Williamson</span>
                  </a>
                </li>
                <li>
                  <a href="single-post.html">
                    <span class="number">5</span>
                    <h3>Life Insurance And Pregnancy: A Working Mom’s Guide</h3>
                    <span class="author">Jenny Wilson</span>
                  </a>
                </li>
              </ul>
            </div>

          </div> <!-- End Trending Section -->
        </div>
      </div>

    </div> <!-- End .row -->
  </div>
</section> <!-- End Post Grid Section -->

<!-- ======= Culture Category Section ======= -->
<section class="category-section">
  <div class="container" data-aos="fade-up">

    <div class="section-header d-flex justify-content-between align-items-center mb-5">
      <h2>Culture</h2>
      <div><a href="category.html" class="more">See All Culture</a></div>
    </div>

    <div class="row">
      <div class="col-md-9">

        <div class="d-lg-flex post-entry-2">
          <a href="single-post.html" class="me-4 thumbnail mb-4 mb-lg-0 d-inline-block">
            <img src="assets/img/post-landscape-6.jpg" alt="" class="img-fluid">
          </a>
          <div>
            <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
            <h3><a href="single-post.html">What is the son of Football Coach John Gruden, Deuce Gruden doing Now?</a></h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Distinctio placeat exercitationem magni voluptates dolore. Tenetur fugiat voluptates quas, nobis error deserunt aliquam temporibus sapiente, laudantium dolorum itaque libero eos deleniti?</p>
            <div class="d-flex align-items-center author">
              <div class="photo"><img src="assets/img/person-2.jpg" alt="" class="img-fluid"></div>
              <div class="name">
                <h3 class="m-0 p-0">Wade Warren</h3>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-4">
            <div class="post-entry-1 border-bottom">
              <a href="single-post.html"><img src="assets/img/post-landscape-1.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2 class="mb-2"><a href="single-post.html">11 Work From Home Part-Time Jobs You Can Do Now</a></h2>
              <span class="author mb-3 d-block">Jenny Wilson</span>
              <p class="mb-4 d-block">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Vero temporibus repudiandae, inventore pariatur numquam cumque possimus</p>
            </div>

            <div class="post-entry-1">
              <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2 class="mb-2"><a href="single-post.html">5 Great Startup Tips for Female Founders</a></h2>
              <span class="author mb-3 d-block">Jenny Wilson</span>
            </div>
          </div>
          <div class="col-lg-8">
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-2.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2 class="mb-2"><a href="single-post.html">How to Avoid Distraction and Stay Focused During Video Calls?</a></h2>
              <span class="author mb-3 d-block">Jenny Wilson</span>
              <p class="mb-4 d-block">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Vero temporibus repudiandae, inventore pariatur numquam cumque possimus</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="post-entry-1 border-bottom">
          <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">How to Avoid Distraction and Stay Focused During Video Calls?</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>

        <div class="post-entry-1 border-bottom">
          <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">17 Pictures of Medium Length Hair in Layers That Will Inspire Your New Haircut</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>

        <div class="post-entry-1 border-bottom">
          <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">9 Half-up/half-down Hairstyles for Long and Medium Hair</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>

        <div class="post-entry-1 border-bottom">
          <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">Life Insurance And Pregnancy: A Working Mom’s Guide</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>

        <div class="post-entry-1 border-bottom">
          <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">The Best Homemade Masks for Face (keep the Pimples Away)</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>

        <div class="post-entry-1 border-bottom">
          <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">10 Life-Changing Hacks Every Working Mom Should Know</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>
      </div>
    </div>
  </div>
</section><!-- End Culture Category Section -->

<!-- ======= Business Category Section ======= -->
<section class="category-section">
  <div class="container" data-aos="fade-up">

    <div class="section-header d-flex justify-content-between align-items-center mb-5">
      <h2>Business</h2>
      <div><a href="category.html" class="more">See All Business</a></div>
    </div>

    <div class="row">
      <div class="col-md-9 order-md-2">

        <div class="d-lg-flex post-entry-2">
          <a href="single-post.html" class="me-4 thumbnail d-inline-block mb-4 mb-lg-0">
            <img src="assets/img/post-landscape-3.jpg" alt="" class="img-fluid">
          </a>
          <div>
            <div class="post-meta"><span class="date">Business</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
            <h3><a href="single-post.html">What is the son of Football Coach John Gruden, Deuce Gruden doing Now?</a></h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Distinctio placeat exercitationem magni voluptates dolore. Tenetur fugiat voluptates quas, nobis error deserunt aliquam temporibus sapiente, laudantium dolorum itaque libero eos deleniti?</p>
            <div class="d-flex align-items-center author">
              <div class="photo"><img src="assets/img/person-4.jpg" alt="" class="img-fluid"></div>
              <div class="name">
                <h3 class="m-0 p-0">Wade Warren</h3>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-4">
            <div class="post-entry-1 border-bottom">
              <a href="single-post.html"><img src="assets/img/post-landscape-5.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Business</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2 class="mb-2"><a href="single-post.html">11 Work From Home Part-Time Jobs You Can Do Now</a></h2>
              <span class="author mb-3 d-block">Jenny Wilson</span>
              <p class="mb-4 d-block">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Vero temporibus repudiandae, inventore pariatur numquam cumque possimus</p>
            </div>

            <div class="post-entry-1">
              <div class="post-meta"><span class="date">Business</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2 class="mb-2"><a href="single-post.html">5 Great Startup Tips for Female Founders</a></h2>
              <span class="author mb-3 d-block">Jenny Wilson</span>
            </div>
          </div>
          <div class="col-lg-8">
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-7.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Business</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2 class="mb-2"><a href="single-post.html">How to Avoid Distraction and Stay Focused During Video Calls?</a></h2>
              <span class="author mb-3 d-block">Jenny Wilson</span>
              <p class="mb-4 d-block">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Vero temporibus repudiandae, inventore pariatur numquam cumque possimus</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="post-entry-1 border-bottom">
          <div class="post-meta"><span class="date">Business</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">How to Avoid Distraction and Stay Focused During Video Calls?</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>

        <div class="post-entry-1 border-bottom">
          <div class="post-meta"><span class="date">Business</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">17 Pictures of Medium Length Hair in Layers That Will Inspire Your New Haircut</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>

        <div class="post-entry-1 border-bottom">
          <div class="post-meta"><span class="date">Business</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">9 Half-up/half-down Hairstyles for Long and Medium Hair</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>

        <div class="post-entry-1 border-bottom">
          <div class="post-meta"><span class="date">Business</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">Life Insurance And Pregnancy: A Working Mom’s Guide</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>

        <div class="post-entry-1 border-bottom">
          <div class="post-meta"><span class="date">Business</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">The Best Homemade Masks for Face (keep the Pimples Away)</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>

        <div class="post-entry-1 border-bottom">
          <div class="post-meta"><span class="date">Business</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">10 Life-Changing Hacks Every Working Mom Should Know</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>
      </div>
    </div>
  </div>
</section><!-- End Business Category Section -->

<!-- ======= Lifestyle Category Section ======= -->
<section class="category-section">
  <div class="container" data-aos="fade-up">

    <div class="section-header d-flex justify-content-between align-items-center mb-5">
      <h2>Lifestyle</h2>
      <div><a href="category.html" class="more">See All Lifestyle</a></div>
    </div>

    <div class="row g-5">
      <div class="col-lg-4">
        <div class="post-entry-1 lg">
          <a href="single-post.html"><img src="assets/img/post-landscape-8.jpg" alt="" class="img-fluid"></a>
          <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2><a href="single-post.html">11 Work From Home Part-Time Jobs You Can Do Now</a></h2>
          <p class="mb-4 d-block">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Vero temporibus repudiandae, inventore pariatur numquam cumque possimus exercitationem? Nihil tempore odit ab minus eveniet praesentium, similique blanditiis molestiae ut saepe perspiciatis officia nemo, eos quae cumque. Accusamus fugiat architecto rerum animi atque eveniet, quo, praesentium dignissimos</p>

          <div class="d-flex align-items-center author">
            <div class="photo"><img src="assets/img/person-7.jpg" alt="" class="img-fluid"></div>
            <div class="name">
              <h3 class="m-0 p-0">Esther Howard</h3>
            </div>
          </div>
        </div>

        <div class="post-entry-1 border-bottom">
          <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">The Best Homemade Masks for Face (keep the Pimples Away)</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>

        <div class="post-entry-1">
          <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
          <h2 class="mb-2"><a href="single-post.html">10 Life-Changing Hacks Every Working Mom Should Know</a></h2>
          <span class="author mb-3 d-block">Jenny Wilson</span>
        </div>

      </div>

      <div class="col-lg-8">
        <div class="row g-5">
          <div class="col-lg-4 border-start custom-border">
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-6.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2><a href="single-post.html">Let’s Get Back to Work, New York</a></h2>
            </div>
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-5.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Jul 17th '22</span></div>
              <h2><a href="single-post.html">How to Avoid Distraction and Stay Focused During Video Calls?</a></h2>
            </div>
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-4.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Mar 15th '22</span></div>
              <h2><a href="single-post.html">Why Craigslist Tampa Is One of The Most Interesting Places On the Web?</a></h2>
            </div>
          </div>
          <div class="col-lg-4 border-start custom-border">
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-3.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2><a href="single-post.html">6 Easy Steps To Create Your Own Cute Merch For Instagram</a></h2>
            </div>
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-2.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Mar 1st '22</span></div>
              <h2><a href="single-post.html">10 Life-Changing Hacks Every Working Mom Should Know</a></h2>
            </div>
            <div class="post-entry-1">
              <a href="single-post.html"><img src="assets/img/post-landscape-1.jpg" alt="" class="img-fluid"></a>
              <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2><a href="single-post.html">5 Great Startup Tips for Female Founders</a></h2>
            </div>
          </div>
          <div class="col-lg-4">

            <div class="post-entry-1 border-bottom">
              <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2 class="mb-2"><a href="single-post.html">How to Avoid Distraction and Stay Focused During Video Calls?</a></h2>
              <span class="author mb-3 d-block">Jenny Wilson</span>
            </div>

            <div class="post-entry-1 border-bottom">
              <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2 class="mb-2"><a href="single-post.html">17 Pictures of Medium Length Hair in Layers That Will Inspire Your New Haircut</a></h2>
              <span class="author mb-3 d-block">Jenny Wilson</span>
            </div>

            <div class="post-entry-1 border-bottom">
              <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2 class="mb-2"><a href="single-post.html">9 Half-up/half-down Hairstyles for Long and Medium Hair</a></h2>
              <span class="author mb-3 d-block">Jenny Wilson</span>
            </div>

            <div class="post-entry-1 border-bottom">
              <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2 class="mb-2"><a href="single-post.html">Life Insurance And Pregnancy: A Working Mom’s Guide</a></h2>
              <span class="author mb-3 d-block">Jenny Wilson</span>
            </div>

            <div class="post-entry-1 border-bottom">
              <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2 class="mb-2"><a href="single-post.html">The Best Homemade Masks for Face (keep the Pimples Away)</a></h2>
              <span class="author mb-3 d-block">Jenny Wilson</span>
            </div>

            <div class="post-entry-1 border-bottom">
              <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
              <h2 class="mb-2"><a href="single-post.html">10 Life-Changing Hacks Every Working Mom Should Know</a></h2>
              <span class="author mb-3 d-block">Jenny Wilson</span>
            </div>

          </div>
        </div>
      </div>

    </div> <!-- End .row -->
  </div>
</section><!-- End Lifestyle Category Section --> 
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
