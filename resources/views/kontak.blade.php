@extends('layouts.user')
@section('title','Kontak')


@section('content')
<!-- ======= Hero Slider Section ======= -->
<section id="hero-slider" class="hero-slider" style="margin-top: 94px;">
    <div class="container-md" data-aos="fade-in">
        <div class="row" style="gap: 1.5rem;">
            <div class="col-md-7 m-0">
                <hr>
                <h2 class="text-primary text-center">Maps</h2>
                <hr>
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d249.35377604788306!2d117.13001198556047!3d-0.5097514697334635!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2df67fb245dc458f%3A0xa8ef3e4834a26bd!2sSekolah%20Kreatif%20SD%20Muhammadiyah%203%20Samarinda!5e0!3m2!1sid!2sid!4v1707661301375!5m2!1sid!2sid"
                    width="645" height="450" style="border:0; margin: 0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <div class="col-md-4">
                <hr>
                <h2 class="text-primary text-center">Contact</h2>
                <hr>
                <div class="row justify-content-center">
                    <div class="form-group text-center mb-3">
                        <a href="" class="btn btn-primary"><i class="bi bi-whatsapp"></i> Admin1</a>
                        <a href="" class="btn btn-primary"><i class="bi bi-whatsapp"></i> Admin2</a>
                    </div>
                    <hr>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Hero Slider Section -->
<section class="contact" style="margin-top: 94px;">
    <div class="container" data-aos="fade-up">
        <div class="form mt-5" style="border-radius: 20px;">
            <form action="{{ route('kritik.saran.store') }}" method="POST" role="form" class="php-email-form" style="border-radius: 20px;">
                @csrf
                <div class="col-md-12 text-center">
                    <h2>Kritik dan Saran</h2>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <input type="text" name="name" class="form-control" id="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group col-md-6">
                        <input type="email" class="form-control" name="email" id="email" placeholder="Your Email"
                            required>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" name="subject" id="subject" placeholder="Subject" required>
                </div>
                <div class="form-group">
                    <textarea class="form-control" name="message" rows="5" placeholder="Message" required></textarea>
                </div>
                <div class="my-3">
                    <div class="loading">Loading</div>
                    <div class="error-message"></div>
                    <div class="sent-message">Your message has been sent. Thank you!</div>
                </div>
                <div class="text-center"><button class="btn btn-primary" type="submit" id="submit_button">Submit</button></div>
            </form>
        </div><!-- End Contact Form -->
    </div>
</section>
@push('js_user')
    <script>
        $(document).ready(function() {

        });
    </script>
@endpush
@endsection
