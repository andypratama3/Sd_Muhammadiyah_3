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
        <h6 class="m-0 font-weight-bold text-primary">Edit Pembayaran {{ $pembayaran->order_id }}</h6>
    </div>
    <hr>
    <div class="card-body">
        <form action="{{ route('dashboard.datamaster.pembayaran.update', $pembayaran->order_id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" value="{{ $pembayaran->order_id }}" name="order_id">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="name">Judul Pembayaran</label>
                        <div class="col-sm-9">
                            <select name="judul_id" class="form-control select2" data-placholder="Pilih Judul Pembayaran">
                                <option value="{{ $pembayaran->judul_id }}" selected>{{ $pembayaran->judul->name }}</option>
                                @foreach ($juduls as $judul)
                                    <option value="{{ $judul->id }}">{{ $judul->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="tgl_lahir">Nama Siswa</label>
                        <div class="col-sm-9">
                            <select class="form-control select2" name="siswa_id" id="">
                                <option selected value="{{ $pembayaran->siswa_id }}">{{ $pembayaran->siswa->name }}</option>
                                @foreach ($siswas as $siswa)
                                <option value="{{ $siswa->id }}">{{ $siswa->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kelas">Kelas</label>
                        <div class="col-sm-9">
                            <select name="kelas_id" id="kelas" class="select2 form-control" data-placholder="Pilih Kelas">
                                <option selected value="{{ $pembayaran->kelas_id }}">{{ $pembayaran->kelas->name }}</option>
                                @foreach ($kelass as $kelas)
                                    <option value="{{ $kelas->id }}" @if (old('kelas_id') == '{{ $kelas->id }}') {{ 'selected' }}  @endif>{{ $kelas->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="category_kelas">Kategori Kelas</label>
                        <div class="col-sm-9">
                            <select name="category_kelas" id="category_kelas" class="select2 form-control" data-placholder="Pilih Kategori Kelas">
                                <option selected value="{{ $pembayaran->category_kelas }}">{{ $pembayaran->category_kelas }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="gross_amount">Total Pembayaran</label>
                        <div class="col-sm-9">
                            <input type="text" name="gross_amount" value="{{ $pembayaran->gross_amount }}" class="form-control" id="gross_amount">
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group row justify-content-end">
                        <a href="{{ route('dashboard.datamaster.siswa.index') }}" class="btn btn-danger float-lg-start mr-2">Kembali</a>
                        <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
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
        $('#kelas').on('change', function () {
            var selectedKelasId = $('#kelas').val();
            var categoryKelasDropdown = $('#category_kelas');
            //clear area dropdown
            categoryKelasDropdown.empty();
            //add option for category_kelas
            categoryKelasDropdown.append('<option selected disabled>Pilih Kategori Kelas</option>');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: '{{ route("dashboard.datasekolah.jadwal.kelas_category") }}',
                method: 'POST',
                data: {
                    id: selectedKelasId
                },
                success: function (response) {
                    let data_category = response.categoryKelas
                    $.each(response, function (index, category) {
                        categoryKelasDropdown.append('<option data-id="' + category  +'" value="' + category + '">' + category + '</option>');
                    });
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });
    });
</script>
@endpush
@endsection
