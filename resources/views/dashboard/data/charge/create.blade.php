@extends('layouts.dashboard')
@section('title', 'Pembayaran')
@section('content')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet"
    type="text/css">
@endpush
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Pembayaran</h6>
    </div>
    <hr>
    <div class="card-body">
        <form action="{{ route('dashboard.datamaster.pembayaran.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="tgl_lahir">Nama Siswa</label>
                        <div class="col-sm-9">
                            <select class="form-control select2" name="siswa_id" id="siswa_id">
                                <option selected disabled>Pilih Siswa</option>
                                @foreach ($siswas as $siswa)
                                    <option value="{{ $siswa->id }}">{{ $siswa->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="gross_amount">Total</label>
                        <div class="col-sm-9">
                            <input type="text" id="gross_amount" class="form-control" name="gross_amount">
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mt-2">
                        <a href="{{ route('dashboard.datamaster.siswa.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
                        <button type="submit" class="btn btn-primary btn-sm float-lg-end">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
<script>
    $(document).ready(function () {
        $('.select2').select2({
            theme: 'bootstrap4'
        });
        $('#gross_amount').on('input', function () {
        let gross_amount = $(this).val();
            gross_amount = gross_amount.replace(/[^0-9.]/g, '');
            gross_amount = formatRupiah(gross_amount);
            $(this).val(gross_amount);
        });

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
