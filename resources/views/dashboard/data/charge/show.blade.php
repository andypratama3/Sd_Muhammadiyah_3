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
        <h6 class="m-0 font-weight-bold text-primary">Detail Pembayaran Dengan Order {{ $charge->order_id }}</h6>
    </div>
    <hr>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mt-2">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="name">Nama</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="name" id="name" value="{{ $charge->name }}"
                            readonly />
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="order_id">Order ID</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="order_id" id="order_id"
                            value="{{ $charge->order_id }}" readonly />
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="siswa_id">Nama Siswa</label>
                    <div class="col-sm-9">
                        <select class="form-control select2" name="siswa_id" id="" disabled>
                            <option selected disabled>{{ $charge->siswa->name }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="gross_amount">Total Pembayaran</label>
                    <div class="col-sm-9">
                        <input type="text" name="gross_amount" value="{{ $charge->gross_amount }}"
                            class="form-control" id="gross_amount" readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="payment_type">Tipe Pembayaran</label>
                    <div class="col-sm-9">
                        <input type="text" name="payment_type" value="{{ $charge->payment_type }}"
                            class="form-control" id="payment_type" readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="bank">Bank</label>
                    <div class="col-sm-9">
                        <input type="text" name="bank" value="{{ $charge->bank }}" class="form-control" id="bank"
                            readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="va_number">Nomor VA</label>
                    <div class="col-sm-9">
                        <input type="text" name="va_number" value="{{ $charge->va_number }}" class="form-control"
                            id="va_number" readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="transaction_id">ID Transaksi</label>
                    <div class="col-sm-9">
                        <input type="text" name="transaction_id" value="{{ $charge->transaction_id }}"
                            class="form-control" id="transaction_id" readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="transaction_time">Tanggal Transaksi</label>
                    <div class="col-sm-9">
                        <input type="text" name="transaction_time" value="{{ $charge->transaction_time }}"
                            class="form-control" id="transaction_time" readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="fraud_status">Status Fraud</label>
                    <div class="col-sm-9">
                        <input type="text" name="fraud_status" value="{{ $charge->fraud_status }}"
                            class="form-control" id="fraud_status" readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="transaction_status">Status Transaksi</label>
                    <div class="col-sm-9">
                        <input type="text" name="transaction_status" value="{{ $charge->transaction_status }}"
                            class="form-control" id="transaction_status" readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mt-2 ">
                <div class="form-group">
                    <a href="{{ route('dashboard.datamaster.charge.index') }}" class="btn btn-danger btn-sm">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
@endpush
@endsection
