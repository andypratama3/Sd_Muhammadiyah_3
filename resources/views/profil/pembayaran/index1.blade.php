@extends('layouts.user')

@section('title','Pembayaran')
@push('css_user')
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
                            <input type="text" class="form-control @error('kode') is-invalid @enderror" id="search"
                                name="kode" placeholder="Masukan Kode Pembayaran" aria-label="Masukan Kode Pembayaran"
                                aria-describedby="button-addon2" value="{{ old('kode') }}">
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

            @forelse($pembayaran as $item)
            <div class="col-lg-12 col-md-6" style="margin-top: 20px;">
                <h3 class="title mb-2">Pembayaran Di Temukan</h3>
                <div class="item">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                           <img src="{{ asset('storage/img/siswa/'. $item->siswa->foto) }}" alt="" srcset="" class="img-fluid">
                                        </div>
                                        <div class="col-md-9">
                                            <div class="form-group mt-2">
                                                <h5>Nama : {{ $item->siswa->name }}</h5>
                                            </div>
                                            <div class="form-group">
                                                <h5>Kode    : {{ $item->order_id }}</h5>
                                            </div>

                                            <div class="form-group">
                                                @if($item->transaction_status == 'pending')
                                                <h5>Status  : <span class="badge bg-warning"><i class="fa-solid fa-clock"></i> {{ $item->status == 'pending' ? 'Belum Lunas' : 'Lunas' }}</span></h5>
                                                @else
                                                <h5>Status  : <span class="badge bg-success"><i class="fa-solid fa-circle-check"></i> {{ $item->status == 'pending' ? 'Belum Lunas' : 'Lunas' }}</span></h5>
                                                @endif
                                            </div>

                                            <div class="form-group">
                                                <h5>Total   : Rp. {{ $item->gross_amount }}</h5>
                                            </div>

                                            <div class="form-group">
                                                <h5>Kategori Pembayaran   : {{ $item->name }}</h5>
                                            </div>
                                            <div class="form-group float-end mt-2">
                                                {{-- @if($item->status == 'pending') --}}
                                                <button class="btn btn-primary" id="pay-button" data-id="{{ $item->order_id }}"><i
                                                        class="fa fa-angle-right"> Bayar</i></>
                                                {{-- @else
                                                <a href="#" class="btn btn-danger" data-id="{{ $item->order_id }}" disabled><i
                                                        class="fa fa-angle-right"> </i>Lunas</a>
                                                @endif --}}
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-lg-12 text-center mb-5" style="margin-top: 100px;">
            <span class="badge bg-danger">Pembayaran Tidak Di Temukan</span><span class="badge bg-primary"></span>
        </div>
        @endforelse

    </div>
</div>
</div>
@push('js_user')
<script type="text/javascript">
    $(document).ready(function () {
        

    });
</script>
@endpush
@endsection
