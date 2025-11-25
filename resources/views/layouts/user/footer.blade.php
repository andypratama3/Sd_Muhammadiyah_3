
<!-- Footer Start -->
<div class="py-5 container-fluid footer wow fadeIn" data-wow-delay="0.2s">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-md-12">
                <div class="mb-5">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-6">
                            <div class="footer-item">
                                <a href="/" class="p-0">
                                    <h3 class="text-white"> SD Muhammadiyah 3 Samarinda</h3>
                                    <img src="{{ asset('asset_new/images/SD3_logo1.png') }}" alt="Logo" class="img-fluid" style="width: 20%;">
                                </a>
                                {{-- <p class="mb-4 text-white">Dolor amet sit justo amet elitr clita ipsum elitr est.Lorem ipsum dolor sit amet, consectetur adipiscing...</p> --}}
                                <div class="mt-4 footer-btn d-flex">
                                    <a class="btn btn-md-square rounded-circle me-3 social-icon" href="https://www.facebook.com/sekolahkreatif.muhammadiyahsamarinda/" target="_blank">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a class="btn btn-md-square rounded-circle me-3 social-icon" href="#">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a class="btn btn-md-square rounded-circle me-3 social-icon" href="https://www.instagram.com/SekolahKreatifSamarinda/" target="_blank">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                    <a class="btn btn-md-square rounded-circle me-0 social-icon" href="https://id.wikipedia.org/wiki/Sd_Muhammadiyah_3_Samarinda" target="_blank">
                                        <i class="fa-brands fa-wikipedia-w"></i>
                                    </a>

                                </div>
                                <div class="mt-4" x-data>
                                    <h5 class="text-white fs-4 fw-bold">Jumlah Pengunjung</h5>
                                    <h5 class="text-white">
                                        Hari Ini:
                                        <span class="text-white" x-data="{ count: 0 }"
                                              x-init="let interval = setInterval(() => {
                                                  if(count < {{ $visitor_by_day }}) count++; else clearInterval(interval);
                                              }, 30)"
                                              x-text="count + 'x'">
                                        </span>
                                    </h5>

                                    <h5 class="text-white">
                                        Bulan Ini:
                                        <span class="text-white" x-data="{ count: 0 }"
                                              x-init="let interval = setInterval(() => {
                                                  if(count < {{ $visitor_by_month }}) count++; else clearInterval(interval);
                                              }, 20)"
                                              x-text="count + 'x'">
                                        </span>
                                    </h5>

                                    <h5 class="text-white">
                                        Tahun Ini:
                                        <span class="text-white" x-data="{ count: 0 }"
                                              x-init="let interval = setInterval(() => {
                                                  if(count < {{ $visitor_by_year }}) count++; else clearInterval(interval);
                                              }, 1)"
                                              x-text="count + 'x'">
                                        </span>
                                    </h5>


                                    <p class="mt-3 text-white">
                                        <strong>
                                            Kami senang Anda berkunjung ke website kami. Semoga Anda menemukan informasi yang bermanfaat dan menarik.
                                        </strong>
                                    </p>
                                </div>


                            </div>

                        </div>
                        <div class="col-md-6 col-lg-6">
                            <h2 class="text-center text-white">Maps</h2>
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d9477.659215305792!2d117.12429426373527!3d-0.5122169736669224!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2df67fb245dc458f%3A0xa8ef3e4834a26bd!2sSekolah%20Kreatif%20SD%20Muhammadiyah%203%20Samarinda!5e0!3m2!1sid!2sid!4v1722696990256!5m2!1sid!2sid"
                                width="100%" height="400" style="border: 0; border-radius: 10px !important;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Footer End -->

<!-- Copyright Start -->
<div class="py-4 container-fluid copyright">
    <div class="container">
        <div class="row g-4 align-items-center">

            <div class="text-center col-md-12 mb-md-0">
                <span class="text-body"><a href="/" class="text-white border-bottom"><i class="fas fa-copyright text-light me-2"></i>SD MUHAMMADIYAH 3 SAMARINDA</a>, All right reserved.</span>

            </div>
            {{-- <div class="text-center col-md-6 text-md-start text-body">

                Develop By <a class="text-white border-bottom" href="https://www.linkedin.com/in/andypratama3/">Andy Pratama</a>
            </div> --}}
        </div>
    </div>
</div>
<!-- Copyright End -->
