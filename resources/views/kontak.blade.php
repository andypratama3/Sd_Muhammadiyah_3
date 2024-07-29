@extends('layouts.user')
@section('title','Kontak')


@section('content')
<div class="contact-us section" id="contact">
    <div class="container">
        <div class="row">
            <div class="col-lg-6  align-self-center">
                <div class="section-heading">
                    <h6>Contact Us</h6>

                    <div class="special-offer">
                        <span class="offer" style="align-items: center !important"><i class="fa-brands fa-whatsapp fa-2x" style="margin-left: 10px; padding-top: 10px !important;"></i></span>
                        <h6><em>Admin:  1</em></h6>
                        <h4>098765679898</h4>
                        <a href="#"><i class="fa fa-angle-right"></i></a>
                    </div>

                    <div class="special-offer">
                        <span class="offer" style="align-items: center !important"><i class="fa-brands fa-whatsapp fa-2x" style="margin-left: 10px; padding-top: 10px !important;"></i></span>
                        <h6>Valide: <em>24 April 2036</em></h6>
                        <h4>Special Offer <em>50%</em> OFF!</h4>
                        <a href="#"><i class="fa fa-angle-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="contact-us-content">
                    <div class="row text-center">
                        <div class="col-lg-12">
                            <h4 class="text-center">Sekolah Kreatif SD Muhammadiyah 3 Samarinda</h4>
                            <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d249.35377604788306!2d117.13001198556047!3d-0.5097514697334635!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2df67fb245dc458f%3A0xa8ef3e4834a26bd!2sSekolah%20Kreatif%20SD%20Muhammadiyah%203%20Samarinda!5e0!3m2!1sid!2sid!4v1707661301375!5m2!1sid!2sid"
                            width="450" height="450" allowfullscreen="" loading="lazy" style="border-radius: 20px;"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="contact-us-content">
                <div class="col-md-12 text-center mb-4">
                    <h2>Kritik dan Saran</h2>
                </div>
                <form id="contact-form" action="{{ route('kritik.saran.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <fieldset>
                                <input type="name" name="name" id="name" placeholder="Your Name..."
                                    autocomplete="on" required>
                            </fieldset>
                        </div>
                        <div class="col-lg-12">
                            <fieldset>
                                <input type="text" name="email" id="email" pattern="[^ @]*@[^ @]*"
                                    placeholder="Your E-mail..." required="">
                            </fieldset>
                        </div>
                        <div class="col-lg-12">
                            <fieldset>
                                <textarea name="message" id="message" placeholder="Your Message"></textarea>
                            </fieldset>
                        </div>
                        <div class="col-lg-12">
                            <fieldset>
                                <button type="submit" id="form-submit" class="orange-button">Send Message
                                    Now</button>
                            </fieldset>
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
