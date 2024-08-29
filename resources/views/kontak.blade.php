@extends('layouts.user')
@section('title','Kontak')


@section('content')
<div class="contact-us section" id="contact">
    <div class="container">
        <div class="row">
            <div class="col-lg-6  align-self-center">
                <div class="section-heading">
                    <h6>Kontak</h6>

                    <div class="special-offer">
                        <a href="https://api.whatsapp.com/send?phone=6281234567898"><i class="fa fa-angle-right"></i>
                        <span class="offer" style="align-items: center !important"><i class="fa-brands fa-whatsapp fa-2x" style="margin-left: 10px; padding-top: 10px !important;"></i></span>
                        <h6><em>Admin:  1</em></h6>
                        <h4>098765679898</h4>
                    </a>
                    </div>
                    <div class="special-offer">
                        <a href="https://api.whatsapp.com/send?phone=6281234567898"><i class="fa fa-angle-right"></i>
                        <span class="offer" style="align-items: center !important"><i class="fa-brands fa-whatsapp fa-2x" style="margin-left: 10px; padding-top: 10px !important;"></i></span>
                        <h6><em>Admin:  2</em></h6>
                        <h4>098765679898</h4>
                    </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="contact-us-content">
                    <div class="row text-center">
                        <div class="col-lg-12">
                            <h4 class="text-center">Sekolah Kreatif SD Muhammadiyah 3 Samarinda</h4>
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d9477.659215305792!2d117.12429426373527!3d-0.5122169736669224!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2df67fb245dc458f%3A0xa8ef3e4834a26bd!2sSekolah%20Kreatif%20SD%20Muhammadiyah%203%20Samarinda!5e0!3m2!1sid!2sid!4v1722696990256!5m2!1sid!2sid" width="600" height="600" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="contact bg-light wow fadeInRight mb-4" data-wow-delay="0.4s">
                <div class="col-md-12 text-center mb-4">
                    <h2>Kritik dan Saran</h2>
                </div>
                <form id="contact-form" action="{{ route('kritik.saran.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control border-0" id="name" name="name" placeholder="Your Name..." required>
                                <label for="name">Your Name</label>
                            </div>

                        </div>
                        <div class="col-lg-12">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control border-0" id="email" name="email" placeholder="Your Email" required>
                                <label for="email">Your Email</label>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-floating mb-3">
                                <textarea class="form-control border-0" id="message" name="message" placeholder="Your Message" style="height: 100px"></textarea>
                                <label for="message">Your Message</label>
                            </div>
                        </div>
                        <div class="col-12">
                                <button type="submit" id="form-submit" class="btn btn-primary w-100 py-3">Send Message
                                    Now</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('js_user')
    <script>
        $(document).ready(function() {

        });
    </script>
@endpush
@endsection
