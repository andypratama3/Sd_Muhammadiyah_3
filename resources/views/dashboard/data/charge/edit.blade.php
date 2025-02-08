@extends('layouts.dashboard')
@section('title', 'Edit Pembayaran')
@section('content')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet"
    type="text/css">
@endpush
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Edit Pembayaran Dengan Virtual Account {{ $charge->va_number }}</h6>
    </div>
    <hr>
    <div class="card-body">
        <form action="{{ route('dashboard.datamaster.charge.update', $charge->id) }}" method="post">
            @csrf
            @method('PUT')
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
                            <select class="form-control" name="transaction_status" id="transaction_status">
                                <option value="pending" {{ $charge->transaction_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="settlement" {{ $charge->transaction_status === 'settlement' ? 'selected' : '' }}>Success</option>
                                <option value="pay_offline" {{ $charge->transaction_status === 'pay_offline' ? 'selected' : '' }}>Pay Offline</option>
                                <option value="failed" {{ $charge->transaction_status === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mt-2 ">
                    <div class="form-group">
                        <a href="{{ route('dashboard.datamaster.charge.index') }}" class="btn btn-danger btn-sm">Kembali</a>
                        <button type="submit" class="btn btn-primary btn-sm float-end">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@push('js')

    <script type="text/javascript">
        $(document).ready(function () {
            $('#gross_amount').on('input', function () {
                let gross_amount = $(this).val();
                gross_amount = gross_amount.replace(/[^0-9.]/g, '');
                gross_amount = formatRupiah(gross_amount);
                $(this).val(gross_amount);
            });

            $('#gross_amount').val(formatRupiah($('#gross_amount').val()));

            // Fungsi untuk memformat angka sebagai mata uang Rupiah
            function formatRupiah(angka) {
                var number_string = angka.toString().replace(/[^,\d]/g, ''),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return rupiah;
            }
        });
    </script>

@endpush
@endsection
