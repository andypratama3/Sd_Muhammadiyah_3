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
        <h6 class="m-0 font-weight-bold text-primary">Tambah Pembayaran</h6>
    </div>
    <hr>
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
                            <input type="text" class="form-control" name="nisn" id="nisn" value="{{ old('nisn') }}"/>
                            <i class="fas fa-solid fa-check bg-success border-1" id="icon-check-nisn" style="font-size: 10px; position : absolute; margin-top: 6px; right: 15px; padding: 10px; border-radius: 50px; color: black; display: none;"></i>
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
                            <input type="text" class="form-control" name="nik" id="nik" value="{{ old('nik') }}"/>
                            <i class="fas fa-solid bg-success border-1" data-toggle="tooltip" title="" id="icon-check-nik" style="font-size: 10px; position : absolute; margin-top: 6px; right: 15px; padding: 10px; border-radius: 50px; color: black; display:none; "></i>
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
                        <label class="col-sm-3 text-dark" for="kelas">Kelas</label>
                        <div class="col-sm-9">
                            <select name="kelas" id="kelas" class="select2 form-control" data-placholder="Pilih Kelas">
                                {{-- <option selected disabled>Pilih Kelas</option>
                                @foreach ($kelass as $kelas)
                                    <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                                @endforeach --}}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="category_kelas">Kategori Kelas</label>
                        <div class="col-sm-9">
                            <select name="category_kelas" id="category_kelas" class="select2 form-control" data-placholder="Pilih Kategori Kelas">
                                <option selected disabled>Pilih Kategori Kelas</option>
                            </select>
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
                                {{-- <option selected disabled>Pilih Provinsi</option>
                                @foreach ($result_provinsi as $provinsi)
                                    <option value="{{ $provinsi['id'] }}">{{ $provinsi['name'] }}</option>
                                @endforeach --}}
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

</script>
@endpush
@endsection
