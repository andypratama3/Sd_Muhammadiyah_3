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
                    <h2 class="text-primary text-center">Form Pesan</h2>
                    <hr>
                    <form action="">
                        <div class="form-group row mb-1">
                            <label for="">Nama</label>
                            <input type="text" class="form-control">
                        </div>
                        <div class="form-group row mb-1">
                            <label for="">Email</label>
                            <input type="text" class="form-control">
                        </div>
                        <div class="form-group row mb-1">
                            <label for="">Hp</label>
                            <input type="text" class="form-control">
                        </div>
                        <div class="form-group row mb-1">
                            <label for="">Pesan</label>
                            <textarea class="form-control" name="" id="" cols="30" rows="5"></textarea>
                        </div>
                        <div class="form-group text-center mt-3">
                            <button type="reset" class="btn btn-danger">Reset</button>
                            <button type="submit" class="btn btn-primary" style="margin-left: 100px;">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Hero Slider Section -->

@push('js')

@endpush
@endsection
