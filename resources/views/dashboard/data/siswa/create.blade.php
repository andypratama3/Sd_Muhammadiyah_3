@extends('layouts.dashboard')
@section('title', 'Tambah Siswa')
@section('content')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet"
    type="text/css">
@endpush
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Siswa</h6>
    </div>
    <hr>
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Siswa</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datamaster.siswa.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="name">Nama</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control"  name="name" id="name" value="{{ old('name') }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="nik">Nisn</label>
                        <div class="col-sm-9 d-flex relative">
                            <input type="text" class="form-control icon-check"  name="nisn" id="nisn" value="{{ old('nisn') }}"/>
                            <i class="fas fa-solid fa-check bg-success border-1" id="icon-check" style="font-size: 10px; position : absolute; margin-top: 6px; right: 15px; padding: 10px; border-radius: 50px; color: black;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="jk">Jenis Kelamin</label>
                        <div class="col-sm-9">
                            <select name="jk" id="jk" class="form-control" value="{{ old('jk') }}">
                                <option selected disabled>Pilih Jenis Kelamin</option>
                                <option value="laki-laki">Laki Laki</option>
                                <option value="perempuan">Perempuan</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="nik">NIK</label>
                        <div class="col-sm-9 d-flex relative">
                            <input type="text" class="form-control icon-check"  name="nik" id="nik" value="{{ old('nik') }}"/>
                            <i class="fas fa-solid fa-check bg-success border-1" id="icon-check" style="font-size: 10px; position : absolute; margin-top: 6px; right: 15px; padding: 10px; border-radius: 50px; color: black;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="tmpt_lahir">Tempat Lahir</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control"  name="tmpt_lahir" id="tmpt_lahir" value="{{ old('tmpt_lahir') }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="tgl_lahir">Tanggal Lahir</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control"  name="tgl_lahir" id="tgl_lahir" value="{{ old('tgl_lahir') }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="agama">Agama</label>
                        <div class="col-sm-9">
                            <select name="agama" id="agama" class="form-control" value="{{ old('agama') }}">
                                <option selected disabled>Pilih Agama</option>
                                <option value="islam">Islam</option>q
                                <option value="kristen">Kristen</option>
                                <option value="katolik">Katolik</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="beasiswa">Beasiswa</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control"  name="beasiswa" id="beasiswa" value="{{ old('beasiswa') }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="name">Foto</label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control"  name="foto" id="foto" value="{{ old('foto') }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <hr>
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
                        <h6 class="m-0 font-weight-bold text-primary">Alamat Tinggal</h6>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="rt">RT</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control"  name="rt" id="rt" value="{{ old('rt') }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="rw">RW</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control"  name="rw" id="rw" value="{{ old('rw') }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="provinsi">Provinsi</label>
                        <div class="col-sm-9">
                            <select name="provinsi_id" id="provinsi" class="select2 form-control" value="{{ old('provinsi_id') }}">
                                <option selected disabled>Pilih Provinsi</option>
                                @foreach ($result_provinsi as $provinsi)
                                    <option value="{{ $provinsi['id'] }}">{{ $provinsi['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kabupaten">Kabupaten / Kota</label>
                        <div class="col-sm-9">
                             <select name="kabupaten_id" id="kabupaten" class="select2 form-control" value="{{ old('kabupaten_id') }}" >
                                <option selected disabled>Pilih Kabupaten</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kecamatan">Kecamatan</label>
                        <div class="col-sm-9">
                             <select name="kecamatan_id" id="kecamatan" class="select2 form-control" value="{{ old('kecamatan_id') }}"  >
                                <option selected disabled>Pilih Kecamatan</option>

                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kelurahan">Kelurahan</label>
                        <div class="col-sm-9">
                            <select name="kelurahan_id" id="kelurahan" class="select2 form-control" value="{{ old('kelurahan_id') }}" >
                                <option selected disabled>Pilih Kelurahan</option>

                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="name">Jenis Tinggal</label>
                        <div class="col-sm-9">
                            <select name="jenis_tinggal" id="jenis_tinggal" class="form-control" value="{{ old('jenis_tinggal') }}" >
                                <option selected disabled>Pilih Jenis Tinggal</option>
                                <option value="Rumah sendiri">Rumah Sendiri</option>
                                <option value="Sewa">Sewa</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="nama_jalan">Nama Jalan</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control"  name="nama_jalan" id="nama_jalan" value="{{ old('nama_jalan') }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="hp">HP Orang Tua</label>
                        <div class="col-sm-9">
                            <input type="tel" class="form-control"  name="no_hp" id="hp" value="{{ old('no_hp') }}" />
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
$(function () {
    $('.select2').select2({
        theme: 'bootstrap4'
    });
    $('#nik').on('input', function () {
        let nik = $('#nik').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: "{{ route('siswa.check.nik') }}",
            data: {
                nik: nik,
            },
            cache: false,
            success: function (response) {
                console.log(response.siswa);
            }
        });
    });
    $('#nisn').on('input', function () {
        let nisn = $('#nisn').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: "{{ route('siswa.check.nisn') }}",
            data: {
                nisn: nisn,
            },
            cache: false,
            success: function (response) {
                console.log(response.siswa);
            }
        });
    });
    $('#provinsi').on('change', function () {
        let provinsi_id = $('#provinsi').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: "{{ route('kabupaten.api') }}",
            data: {
                provinsi_id: provinsi_id,
            },
            cache: false,
            success: function (response) {
                const kabupaten = response.data;
                let selectElement = $('#kabupaten');
                selectElement.empty();
                selectElement.append('<option value="">Pilih Kabupaten</option>');
                $.each(kabupaten, function(i, item) {
                    selectElement.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
            },
        });
    });
    $('#kabupaten').on('change', function () {
        let regency_id = $('#kabupaten').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: "{{ route('kecamatan.api') }}",
            data: {
                regency_id: regency_id,
            },
            cache: false,
            success: function (response) {
            const kota = response.data;
            let selectElement = $('#kecamatan');
            selectElement.empty();
            selectElement.append('<option value="">Pilih Kecamatan</option>');
            $.each(kota, function(i, item) {
                selectElement.append('<option value="' + item.id + '">' + item.name + '</option>');
            });
            },
        });
    }); 
    $('#kecamatan').on('change', function () {
        let district_id = $('#kecamatan').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: "{{ route('kelurahan.api') }}",
            data: {
                district_id: district_id,
            },
            cache: false,
            success: function (response) {
            const kota = response.data;
            let selectElement = $('#kelurahan');
            selectElement.empty();
            selectElement.append('<option value="">Pilih Kelurahan</option>');
            $.each(kota, function(i, item) {
                selectElement.append('<option value="' + item.id + '">' + item.name + '</option>');
            });
            },
        });
    });
});
</script>
@endpush
@endsection
