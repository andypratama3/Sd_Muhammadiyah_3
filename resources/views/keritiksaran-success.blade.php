@extends('layouts.user')
@section('title','Kritik dan Saran')
@push('front_css')


@endpush

@section('content')
<div class="contact-us section" id="contact">
    <div class="container">
        <div class="row">
            <div class="card mb-5" style="background: none !important;">
                <div class="card-body text-center">
                    <div class="img">
                        <img src="{{ asset('asset_new/images/SD3_logo1.png') }}" alt="" srcset="" class="img-fluid">
                    </div>
                    <h3 class="text-center mt-2">Terimakasih Atas Kritik dan Saran Anda</h3>
                    <a href="{{ route('index') }}" class="btn btn-primary">Kembali</a>
                </div>
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
