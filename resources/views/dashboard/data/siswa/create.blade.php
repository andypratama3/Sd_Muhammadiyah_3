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
        <form id="form_siswa" action="{{ route('dashboard.datamaster.siswa.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="name">Nama</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="nik">Nisn</label>
                        <div class="col-sm-9 d-flex relative">
                            <input type="text" class="form-control" name="nisn" id="nisn" value="{{ old('nisn') }}" />
                            <i class="fas fa-solid fa-check bg-success border-1" id="icon-check-nisn"
                                style="font-size: 10px; position : absolute; margin-top: 6px; right: 15px; padding: 10px; border-radius: 50px; color: black; display: none;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="jk">Jenis Kelamin</label>
                        <div class="col-sm-9">
                            <select name="jk" id="jk" class="form-control" value="{{ old('jk') }}">
                                <option selected disabled>Pilih Jenis Kelamin</option>
                                <option value="laki-laki" @if (old('jk') == "laki-laki") {{ 'selected' }} @endif>Laki Laki</option>
                                <option value="perempuan" @if (old('jk') == "perempuan") {{ 'selected' }} @endif>Perempuan</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="tmpt_lahir">Tempat Lahir</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="tmpt_lahir" id="tmpt_lahir"
                                value="{{ old('tmpt_lahir') }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="tgl_lahir">Tanggal Lahir</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" name="tgl_lahir" id="tgl_lahir"
                                value="{{ old('tgl_lahir') }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="agama">Agama</label>
                        <div class="col-sm-9">
                            <select name="agama" id="agama" class="form-control" value="{{ old('agama') }}">
                                <option selected disabled>Pilih Agama</option>
                                <option value="islam" @if (old('agama') == "islam") {{ 'selected' }} @endif>Islam</option>
                                <option value="kristen" @if (old('agama') == "kristen") {{ 'selected' }} @endif>Kristen</option>
                                <option value="katolik" @if (old('agama') == "katolik") {{ 'selected' }} @endif>Katolik</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kelas_tahun">Nama Pendidikan</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="nama_pendidikan" id="nama_pendidikan"
                                value="{{ old('nama_pendidikan') }}" placeholder="Nama Pendidikan Sebelumnya | Boleh Di Kosongkan" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kelas_tahun">Alamat Pendidikan</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="nama_jalan_pendidikan" id="nama_jalan_pendidikan"
                                value="{{ old('nama_jalan_pendidikan') }}"  placeholder="Alamat Pendidikan Sebelumnya | Boleh Di Kosongkan" />
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kelas_tahun">Kelas  / Tahun Ajaran</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="kelas_tahun" id="kelas_tahun"
                                value="{{ old('kelas_tahun') }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="tanggal_masuk">Tanggal Masuk</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" name="tanggal_masuk" id="tanggal_masuk"
                                value="{{ old('tanggal_masuk') }}" />
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="beasiswa">Beasiswa</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="beasiswa" id="beasiswa"
                                value="{{ old('beasiswa') }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kelas">Kelas</label>
                        <div class="col-sm-9">
                            <select name="kelas" id="kelas" class="select2 form-control" data-placholder="Pilih Kelas">
                                <option selected disabled>Pilih Kelas</option>
                                @foreach ($kelass as $kelas)
                                <option value="{{ $kelas->id }}" @if (old('kelas') == "{{ $kelas->id }}") {{ 'selected' }} @endif  >{{ $kelas->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="category_kelas">Kategori Kelas</label>
                        <div class="col-sm-9">
                            <select name="category_kelas" id="category_kelas" class="select2 form-control"
                                data-placholder="Pilih Kategori Kelas">
                                <option selected disabled>Pilih Kategori Kelas</option>

                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="name">Foto</label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control" name="foto" id="foto" value="{{ old('foto') }}" required/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="spp">SPP</label>
                        <div class="col-sm-9">
                        <input type="text" name="spp" id="spp" class="form-control" value="{{ old('spp') }}">
                        </div>
                    </div>
                </div>
                {{-- Data Detail orang tua --}}
                <div class="col-md-12">
                    <hr>
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary text-center" id="title_data_orang_tua">Data Orang Tua</h6>
                        <div class="col-md-2">
                        <select id="data_wali" name="data_wali" class="btn btn-primary mb-2 form-control" style="border:2px;">
                            <option selected>Pilih Data</option>
                            <option value="orang_tua" {{ old('data_wali') == 'orang_tua' ? 'selected' : '' }}>Orang Tua</option>
                            <option value="wali" {{ old('data_wali') == 'wali' ? 'selected' : '' }}>Wali</option>
                        </select>
                    </div>
                    </div>
                </div>
                <div class="row" id="orang_tua" style="display: none;">
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="nama_ayah">Nama Ayah</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="nama_ayah" id="nama_ayah" value="{{ old('nama_ayah') }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="nama_ibu">Nama Ibu</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="nama_ibu" id="nama_ibu" value="{{ old('nama_ibu') }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="pendidikan_ayah">Pendidikan Ayah</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="pendidikan_ayah" id="pendidikan_ayah" value="{{ old('pendidikan_ayah') }}" />

                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="pendidikan_ibu">Pendidikan Ibu</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="pendidikan_ibu" id="pendidikan_ibu" value="{{ old('pendidikan_ibu') }}" />

                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="pendidikan_ayah">Pekerjaan Ayah</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="pekerjaan_ayah" id="pekerjaan_ayah" value="{{ old('pekerjaan_ayah') }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="pekerjaan_ibu">Pekerjaan Ibu</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="pekerjaan_ibu" id="pekerjaan_ibu" value="{{ old('pekerjaan_ibu') }}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row " id="wali" style="display: none;">
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="nama_wali">Nama Wali</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="nama_wali" id="nama_wali" value="{{ old('nama_wali') }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="pekerjaan_wali">Pekerjaan Wali</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="pekerjaan_wali" id="pekerjaan_wali" value="{{ old('pekerjaan_wali') }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="alamat_wali">Alamat Wali</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="alamat_wali" id="alamat_wali" value="{{ old('alamat_wali') }}" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Data Detail lSiswa --}}
                <div class="col-md-12">
                    <hr>
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
                        <h6 class="m-0 font-weight-bold text-primary">Alamat Tinggal</h6>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="rt">RT</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="rt" id="rt" value="{{ old('rt') }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="rw">RW</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="rw" id="rw" value="{{ old('rw') }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="provinsi">Provinsi</label>
                        <div class="col-sm-9">
                            <select name="provinsi_id" id="provinsi" class="select2 form-control"
                                value="{{ old('provinsi_id') }}">
                                <option selected disabled>Pilih Provinsi</option>
                                @foreach ($result_provinsi as $provinsi)
                                <option value="{{ $provinsi['id'] }}" {{ old('provinsi_id') == $provinsi['id'] ? 'selected' : '' }}>
                                    {{ $provinsi['name'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kabupaten">Kabupaten / Kota</label>
                        <div class="col-sm-9">
                            <select name="kabupaten_id" id="kabupaten" class="select2 form-control"
                                value="{{ old('kabupaten_id') }}">
                                <option selected disabled>Pilih Kabupaten</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kecamatan">Kecamatan</label>
                        <div class="col-sm-9">
                            <select name="kecamatan_id" id="kecamatan" class="select2 form-control"
                                value="{{ old('kecamatan_id') }}">
                                <option selected disabled>Pilih Kecamatan</option>

                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kelurahan">Kelurahan</label>
                        <div class="col-sm-9">
                            <select name="kelurahan_id" id="kelurahan" class="select2 form-control"
                                value="{{ old('kelurahan_id') }}">
                                <option selected disabled>Pilih Kelurahan</option>

                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="name">Jenis Tinggal</label>
                        <div class="col-sm-9">
                            <select name="jenis_tinggal" id="jenis_tinggal" class="form-control"
                                value="{{ old('jenis_tinggal') }}">
                                <option selected disabled>Pilih Jenis Tinggal</option>
                                <option value="Rumah Sendiri" @if (old('jenis_tinggal') == 'Rumah Sendiri') {{ 'selected' }} @endif>Rumah Sendiri</option>
                                <option value="Sewa" @if (old('jenis_tinggal') == 'sewa') {{ 'selected' }} @endif>Sewa</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="nama_jalan">Nama Jalan</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="nama_jalan" id="nama_jalan"
                                value="{{ old('nama_jalan') }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="no_hp">No HP</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="no_hp" id="no_hp" value="{{ old('no_hp') }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mt-2">
                    <a href="{{ route('dashboard.datamaster.siswa.index') }}" class="btn btn-danger btn-sm">Kembali</a>
                    <button type="submit" class="btn btn-primary float-lg-end btn-sm">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>
@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>

<script>
    $(function () {
        //property
        let nik_property = document.getElementById('icon-check-nik');
        let nisn_property = document.getElementById('icon-check-nisn');

        function formdata_data_wali() {
            const title = $('#title_data_orang_tua');
            let wali = $('#wali');
            let orang_tua = $('#orang_tua');
            let data = $('#data_wali').val();
            if(data === 'wali'){
              title.text('Data Wali');
              wali.css('display', '');
              orang_tua.css('display', 'none');
            }else if(data == 'orang_tua'){
                title.text('Data Orang Tua');
                wali.css('display', 'none');
                orang_tua.css('display', '');
            }else{
              title.text('Pilih Data Orang Tua');
                wali.css('display', 'none');
                orang_tua.css('display', 'none');

            }
        }

        $('.select2').select2({
            theme: 'bootstrap4'
        });
        $('#data_wali').on('change', function () {
            formdata_data_wali();
        });
        formdata_data_wali();

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
                        categoryKelasDropdown.append('<option data-id="' +
                            category + '" value="' + category + '">' +
                            category + '</option>');
                    });
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });
        $('#nisn').on('input', function () {
            let inputValue = $('#nisn').val();
            if (inputValue.length < 16) {
                nisn_property.className = 'fas fa-solid fa-xmark bg-danger border-1';
                nisn_property.style.display = 'block';
                $('#icon-check-nisn').attr('title', 'Nisn Harus 16 Karakter');
            } else {
                nisn_property.className = 'fas fa-solid fa-check bg-success border-1';
                nisn_property.style.display = 'block';
                $('#icon-check-nisn').attr('title', '');
            }
            if (inputValue.length > 16) {
                inputValue = inputValue.substring(0, 16);
                $(this).val(inputValue);
            }
        });
        $('#nisn').on('change', function () {
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
                    if (nisn.length < 16) {
                        nisn_property.className = 'fas fa-solid fa-xmark bg-danger border-1';
                        nisn_property.style.display = 'block';
                        $('#icon-check-nisn').attr('title', 'Nisn Harus 16 Karakter');
                    }else {
                        if (response[0] === 'error') {
                            nisn_property.className = 'fas fa-solid fa-xmark bg-danger border-1';
                            nisn_property.style.display = 'block';
                            $('#icon-check-nisn').attr('title', 'Nisn Telah Ada');
                            $('#nisn').addClass('border-danger');
                        } else {
                            nisn_property.className = 'fas fa-solid fa-check bg-success border-1';
                            nisn_property.style.display = 'block';
                            $('#icon-check-nisn').attr('title', 'Nisn Bisa Di Gunakan');
                            $('#nisn').removeClass('border-danger');
                            $('#nisn').addClass('border-success');
                        }
                    }

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
                    $.each(kabupaten, function (i, item) {
                        selectElement.append('<option value="' + item.id + '">' +
                            item.name + '</option>');
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
                    $.each(kota, function (i, item) {
                        selectElement.append('<option value="' + item.id + '">' +
                            item.name + '</option>');
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
                    $.each(kota, function (i, item) {
                        selectElement.append('<option value="' + item.id + '">' + item.name + '</option>');
                    });
                },
            });
        });

        //function formatRupiah
        $('#spp').on('input', function () {
        let spp = $(this).val();
            spp = spp.replace(/[^0-9.]/g, '');
            spp = formatRupiah(spp);
            $(this).val(spp);
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
