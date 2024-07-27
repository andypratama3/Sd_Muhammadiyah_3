@extends('layouts.user_new')

@section('title','Pembayaran')
@push('css_user')
    {{-- <style>
        .events .button-pay{
            width: 100px !important;
            height: max-content;
        }
    </style> --}}
@endpush
@section('content')

<div class="section events" id="events">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="section-heading">
                    <h6>Pembayaran</h6>
                    <h2>Masukan Kode Pembayaran</h2>
                    <div class="col-md-2">

                    </div>
                    <form action="{{ route('pembayaran.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" class="form-control @error('kode') is-invalid @enderror" id="search" name="kode" placeholder="Masukan Kode Pembayaran" aria-label="Masukan Kode Pembayaran" aria-describedby="button-addon2">
                            <button class="btn btn-success" type="submit" id="button-addon2">Cari Pembayaran</button>
                            @error('kode')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>

            @forelse ($pembayaran as $item)
            <div class="col-lg-12 col-md-6 m-0">
                <h3 class="title mb-2">Pembayaran Di Temukan</h3>
                <div class="item">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="image">
                                <img src="{{ asset('asset_new/images/SD3_logo.png') }}" alt="" style="width: 40%; margin-top: 25px; padding-top: 8px; ">
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <ul>
                                <li>
                                    <span class="category" style="font-size: 20px;">{{ $item->judul->name }}</span>
                                    <h4>{{ $item->title }}</h4>
                                </li>
                                <li>
                                    <span>Nama Siswa :</span>
                                    <h6>{{ $item->siswa->name }}</h6>
                                </li>
                                <li>
                                    <span>Kelas :</span>
                                    <h6>{{ $item->category_kelas }}</h6>
                                </li>
                                <li>
                                    <span>Total :</span>
                                    <h6>Rp .{{ $item->gross_amount }}</h6>
                                </li>

                            </ul>
                            @if($item->status == 'pending')
                            <a href="#" class="button-pay" data-id="{{ $item->order_id }}"><i class="fa fa-angle-right"> Bayar</i></a>
                            @else
                            <a href="#" class="button-pay" data-id="{{ $item->order_id }}" disabled><i class="fa fa-angle-right"> Bayar</i></a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
                <div class="col-lg-12 text-center  mb-5 ">
                    <span class="badge bg-danger">Pembayaran Tidak Di Temukan</span><span class="badge bg-primary"></span>
                </div>
            @endforelse

        </div>
    </div>
</div>
@push('js_user')
<script>
    $(document).ready(function () {
        $('.events').on('click', '.button-pay', function () {
            let payment_id = $(this).data('id');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "POST",
                url: "{{ route('pembayaran.pay') }}",
                data: {
                    order_id : payment_id,
                },
                success: function (response) {
                    window.location.href = response.redirect;
                }

            });
        });
    });
</script>
@endpush
@endsection
