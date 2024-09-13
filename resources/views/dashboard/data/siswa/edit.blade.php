@extends('layouts.dashboard')
@section('title', 'Edit Siswa')
@section('content')
    @push('css')
        <link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet"
            type="text/css">
    @endpush
    <div class="card mb-4">
        @include('layouts.flashmessage')
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Edit Siswa</h6>
        </div>
        <hr>
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
            <h6 class="m-0 font-weight-bold text-primary">Data Siswa</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('dashboard.datamaster.siswa.update', $siswa->slug) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="slug" value="{{ $siswa->slug }}">

                <div class="row">
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="name">Nama</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="name" id="name"
                                    value="{{ $siswa->name }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="nik">Nisn</label>
                            <div class="col-sm-9 d-flex relative">
                                <input type="text" class="form-control" name="nisn" id="nisn"
                                    value="{{ $siswa->nisn }}" />
                                <i class="fas fa-solid fa-check bg-success border-1" id="icon-check-nisn"
                                    style="font-size: 10px; position : absolute; margin-top: 6px; right: 15px; padding: 10px; border-radius: 50px; color: black; display: none;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="jk">Jenis Kelamin</label>
                            <div class="col-sm-9">
                                <select name="jk" id="jk" class="form-control" value="{{ $siswa->name }}">
                                    <option selected value="{{ $siswa->jk }}">{{ $siswa->jk }}</option>
                                    <option value="laki-laki">Laki Laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="tmpt_lahir">Tempat Lahir</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="tmpt_lahir" id="tmpt_lahir"
                                    value="{{ $siswa->tmpt_lahir }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="tgl_lahir">Tanggal Lahir</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" name="tgl_lahir" id="tgl_lahir"
                                    value="{{ $siswa->tgl_lahir }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="agama">Agama</label>
                            <div class="col-sm-9">
                                <select name="agama" id="agama" class="form-control">
                                    <option selected value="{{ $siswa->agama }}">{{ $siswa->agama }}</option>
                                    <option value="islam">Islam</option>
                                    <option value="kristen">Kristen</option>
                                    <option value="katolik">Katolik</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="kelas_tahun">Nama Pendidikan</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="nama_pendidikan" id="nama_pendidikan"
                                    value="{{ $siswa->nama_pendidikan }}" placeholder="Nama Pendidikan Sebelumnya | Boleh Di Kosongkan" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="kelas_tahun">Alamat Pendidikan</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="nama_jalan_pendidikan" id="nama_jalan_pendidikan"
                                    value="{{ $siswa->nama_jalan_pendidikan }}"  placeholder="Alamat Pendidikan Sebelumnya | Boleh Di Kosongkan" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="kelas_tahun">Kelas  / Tahun Ajaran</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="kelas_tahun" id="kelas_tahun"
                                    value="{{ $siswa->kelas_tahun }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="tanggal_masuk">Tanggal Masuk</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" name="tanggal_masuk" id="tanggal_masuk"
                                    value="{{ $siswa->tanggal_masuk }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="beasiswa">Beasiswa</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="beasiswa" id="beasiswa"
                                    value="{{ $siswa->beasiswa }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="kelas">Kelas</label>
                            <div class="col-sm-9">
                                <select name="kelas" id="kelas" class="select2 form-control"
                                    data-placholder="Pilih Kelas">
                                    @foreach ($siswa->kelas as $kelas)
                                        <option selected value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                                    @endforeach
                                    @foreach ($kelass as $kelas)
                                        <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
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
                                    @foreach ($siswa->kelas as $kelas)
                                        <option selected value="{{ $kelas->pivot->category_kelas }}">
                                            {{ $kelas->pivot->category_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="spp">SPP</label>
                            <div class="col-sm-9">
                            <input type="text" name="spp" id="spp" class="form-control" value="{{ old('spp', $siswa->spp) }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="name">Foto</label>
                            <div class="col-sm-9">
                                <div class="input-group ">
                                    <input type="file" class="form-control" name="foto" id="foto"
                                        value="{{ $siswa->foto }}"/>
                                    </div>
                                    <div class="input-group-append">
                                        <a target="__blank" href="{{ asset('storage/img/siswa/' . $siswa->foto) }}" class="btn btn-success mt-2"><i class="bi bi-file"></i> Lihat Foto</a>
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
                                <input type="text" class="form-control" name="rt" id="rt"
                                    value="{{ $siswa->rt }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="rw">RW</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="rw" id="rw"
                                    value="{{ $siswa->rw }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="provinsi">Provinsi</label>
                            <div class="col-sm-9">
                                <select name="provinsi_id" id="provinsi" class="select2 form-control">
                                    <option id="provinsi_select" value="{{ $siswa->provinsi_id }}" selected></option>
                                    @foreach ($result_provinsi as $provinsi)
                                        <option value="{{ $provinsi['id'] }}">{{ $provinsi['name'] }}</option>
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
                                    data-placholder="Pilih Kabupaten">
                                    <option selected value="{{ $siswa->kabupaten_id }}"></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="kecamatan">Kecamatan</label>
                            <div class="col-sm-9">
                                <select name="kecamatan_id" id="kecamatan" class="select2 form-control"
                                    data-placholder="Pilih Kecamatan">
                                    <option selected value="{{ $siswa->kecamatan_id }}"></option>

                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="kelurahan">Kelurahan</label>
                            <div class="col-sm-9">
                                <select name="kelurahan_id" id="kelurahan" class="select2 form-control"
                                    data-placholder="Pilih Kelurahan">
                                    <option selected value="{{ $siswa->kelurahan_id }}"></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="name">Jenis Tinggal</label>
                            <div class="col-sm-9">
                                <select name="jenis_tinggal" id="jenis_tinggal" class="form-control"
                                    value="{{ $siswa->name }}">
                                    <option selected value="{{ $siswa->jenis_tinggal }}">{{ $siswa->jenis_tinggal }}
                                    </option>
                                    <option value="Rumah sendiri">Rumah Sendiri</option>
                                    <option value="Sewa">Sewa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="nama_jalan">Nama Jalan</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="nama_jalan" id="nama_jalan"
                                    value="{{ $siswa->nama_jalan }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="form-group row">
                            <label class="col-sm-3 text-dark" for="hp">HP Orang Tua</label>
                            <div class="col-sm-9">
                                <input type="tel" class="form-control" name="no_hp" id="hp"
                                    value="{{ $siswa->no_hp }}" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group row justify-content-end">
                            <a href="{{ route('dashboard.datamaster.siswa.index') }}"
                                class="btn btn-danger float-lg-start mr-2">Kembali</a>
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
            $(document).ready(function() {
                async function getProvinsi() {
                    provinsi_id = $('#provinsi').val();
                    kabupaten_id = $('#kabupaten').val();
                    kecamatan_id = $('#kecamatan').val();
                    kelurahan_id = $('#kelurahan').val();
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: "POST",
                        url: "{{ route('getprovinsi.api') }}",
                        data: {
                            provinsi_id: provinsi_id,
                            kabupaten_id: kabupaten_id,
                            kecamatan_id: kecamatan_id,
                            kelurahan_id: kelurahan_id,

                        },
                        dataType: "json",
                        success: function(response) {
                            provinsi_id = response.provinsi.id;
                            kabupaten_id = response.kabupaten.id;
                            kecamatan_id = response.kecamatan.id;
                            kelurahan_id = response.kelurahan.id;
                            provinsi_name = response.provinsi.name;
                            kabupaten_name = response.kabupaten.name;
                            kecamatan_name = response.kecamatan.name;
                            kelurahan_name = response.kelurahan.name;
                            // $('#kabupaten').attr('value',kabupaten_id);
                            // $('#kecamatan').attr('value',kecamatan_id);
                            // $('#kelurahan').attr('value',kelurahan_id);
                            //provinsi
                            let $selected_provinsi = $("<option selected='selected'></option>").val(
                                provinsi_id).text(provinsi_name);
                            $("#provinsi").append($selected_provinsi).trigger('change');
                            //kabupaten
                            let $selected_kabupaten = $("<option selected='selected'></option>").val(
                                kabupaten_id).text(kabupaten_name);
                            $("#kabupaten").append($selected_kabupaten).trigger('change');
                            //kecamatan
                            let $selected_kecamatan = $("<option selected='selected'></option>").val(
                                kecamatan_id).text(kecamatan_name);
                            $("#kecamatan").append($selected_kecamatan).trigger('change');
                            //kelurahan
                            let $selected_kelurahan = $("<option selected='selected'></option>").val(
                                kelurahan_id).text(kelurahan_name);
                            $("#kelurahan").append($selected_kelurahan).trigger('change');


                        }
                    });
                }
                getProvinsi();
            });
        </script>
        <script>
            $(function() {
                //property
                let nik_property = document.getElementById('icon-check-nik');
                let nisn_property = document.getElementById('icon-check-nisn');
                $('.select2').select2({
                    theme: 'bootstrap4'
                });
                $('#kelas').on('change', function() {
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
                        url: '{{ route('dashboard.datasekolah.jadwal.kelas_category') }}',
                        method: 'POST',
                        data: {
                            id: selectedKelasId
                        },
                        success: function(response) {
                            let data_category = response.categoryKelas
                            $.each(response, function(index, category) {
                                categoryKelasDropdown.append('<option data-id="' +
                                    category + '" value="' + category + '">' +
                                    category + '</option>');
                            });
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                });
                $('#nik').on('input', function() {
                    let inputValue = $('#nik').val();
                    if (inputValue.length < 16) {
                        nik_property.className = 'fas fa-solid fa-xmark bg-danger border-1';
                        nik_property.style.display = 'block';
                        $('#icon-check-nik').attr('title', 'Nik Harus 16 Karakter');
                    } else if (inputValue.length > 16) {
                        $('#icon-check-nik').attr('title', 'Nik Lebih dari 16 Karakter');
                    } else {
                        nik_property.className = 'fas fa-solid fa-check bg-success border-1';
                        nik_property.style.display = 'block';
                        $('#icon-check-nik').attr('title', '');
                    }

                    // If you want to limit the input length to 16 characters, truncate the input
                    if (inputValue.length > 16) {
                        inputValue = inputValue.substring(0, 16);
                        $(this).val(inputValue);
                    }
                });
                $('#nisn').on('input', function() {
                    let inputValue = $('#nisn').val();
                    if (inputValue.length < 16) {
                        nisn_property.className = 'fas fa-solid fa-xmark bg-danger border-1';
                        nisn_property.style.display = 'block';
                        $('#icon-check-nisn').attr('title', 'Nisn Harus 16 Karakter');
                    } else if (inputValue.length > 16) {
                        $('#icon-check-nisn').attr('title', 'Nisn Lebih dari 16 Karakter');
                    } else {
                        nisn_property.className = 'fas fa-solid fa-check bg-success border-1';
                        nisn_property.style.display = 'block';
                        $('#icon-check-nisn').attr('title', '');
                    }

                    // If you want to limit the input length to 16 characters, truncate the input
                    if (inputValue.length > 16) {
                        inputValue = inputValue.substring(0, 16);
                        $(this).val(inputValue);
                    }
                });

                $('#nisn').on('change', function() {
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
                        success: function(response) {

                            if (response[0] === 'error') {
                                nisn_property.className =
                                'fas fa-solid fa-xmark bg-danger border-1';
                                nisn_property.style.display = 'block';
                                $('#icon-check-nisn').attr('title', 'Nisn Telah Ada');
                                $('#nisn').addClass('border-danger');
                            } else {
                                nisn_property.className =
                                    'fas fa-solid fa-check bg-success border-1';
                                nisn_property.style.display = 'block';
                                $('#icon-check-nisn').attr('title', 'Nisn Bisa Di Gunakan');
                                $('#nisn').removeClass('border-danger');
                                $('#nisn').addClass('border-success');
                            }
                        }
                    });
                });
                $('#provinsi').on('change', function() {
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
                        success: function(response) {
                            const kabupaten = response.data;
                            let selectElement = $('#kabupaten');
                            selectElement.append('<option value="">Pilih Kabupaten</option>');
                            $.each(kabupaten, function(i, item) {
                                selectElement.append('<option value="' + item.id + '">' +
                                    item.name + '</option>');
                            });
                        },
                    });
                });
                $('#kabupaten').on('change', function() {
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
                        success: function(response) {
                            const kota = response.data;
                            let selectElement = $('#kecamatan');
                            selectElement.append('<option value="">Pilih Kecamatan</option>');
                            $.each(kota, function(i, item) {
                                selectElement.append('<option value="' + item.id + '">' +
                                    item.name + '</option>');
                            });
                        },
                    });
                });
                $('#kecamatan').on('change', function() {
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
                        success: function(response) {
                            const kota = response.data;
                            let selectElement = $('#kelurahan');
                            selectElement.append('<option value="">Pilih Kelurahan</option>');
                            $.each(kota, function(i, item) {
                                selectElement.append('<option value="' + item.id + '">' +
                                    item.name + '</option>');
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

                $('#spp').val(formatRupiah($('#spp').val()));

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
