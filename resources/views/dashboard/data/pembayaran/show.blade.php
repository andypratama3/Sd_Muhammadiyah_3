@extends('layouts.dashboard')
@section('title', 'Detail Pembayaran')
@section('content')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet"
    type="text/css">
@endpush
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Detail Pembayaran Dengan Order {{ $pembayaran->order_id }}</h6>
    </div>
    <hr>
    <div class="card-body">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="name">Judul Pembayaran</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="name" id="name" value="{{ $pembayaran->name }}"
                            readonly />
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="tgl_lahir">Nama Siswa</label>
                    <div class="col-sm-9">
                        <select class="form-control select2" name="siswa_id" id="" disabled>
                            <option selected disabled>{{ $pembayaran->siswa->name }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="kelas">Kelas</label>
                    <div class="col-sm-9">
                        <select name="kelas_id" id="kelas" class="select2 form-control" data-placholder="Pilih Kelas"
                            disabled>
                            <option selected disabled>{{ $pembayaran->kelas->name }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="category_kelas">Kategori Kelas</label>
                    <div class="col-sm-9">
                        <select name="category_kelas" id="category_kelas" class="select2 form-control" disabled>
                            <option selected disabled>{{ $pembayaran->category_kelas }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="gross_amount">Total Pembayaran</label>
                    <div class="col-sm-9">
                        <input type="text" name="gross_amount" value="{{ $pembayaran->gross_amount }}"
                            class="form-control" id="gross_amount" readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="gross_amount">Status</label>
                    <div class="col-sm-9">
                        @if ($pembayaran->status == 'success')
                        <h5 class="text-black"><span class="badge bg-success"><i class="fa-solid fa-clock"></i>
                                {{ $pembayaran->status }}</span></h5>
                        @elseif ($pembayaran->status == 'pending')
                        <h5 class="text-black"><span class="badge bg-warning"><i class="fa-solid fa-clock"></i>
                                {{ $pembayaran->status }}</span></h5>
                        @elseif ($pembayaran->status == 'error')
                        <h5 class="text-black"><span class="badge bg-warning"><i class="fa-solid fa-clock"></i>
                                {{ $pembayaran->status }}</span></h5>
                        @else
                        <h5 class="text-black"><span class="badge bg-danger"><i class="fa-solid fa-clock"></i>
                                {{ $pembayaran->status }}</span></h5>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <a href="{{ route('dashboard.datamaster.pembayaran.index') }}" class="btn btn-danger">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
@endpush
@endsection
