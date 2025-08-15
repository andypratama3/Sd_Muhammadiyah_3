@extends('layouts.dashboard')
@section('title', "Edit SPMB Siswa $spmb->name")

@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title">Edit Data SPMB</h4>
            <a href="{{ route('dashboard.datamaster.pembayaran.create') }}" class="float-right btn btn-success btn-sm">
                Tambah <i class="fas fa-plus"></i>
            </a>
        </div>
        <div class="card-body">
            @include('layouts.flashmessage')
            <form action="{{ route('dashboard.spmb.update', $spmb->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Order ID</label>
                    <input type="text" name="order_id" value="{{ old('order_id', $spmb->order_id) }}" class="form-control @error('order_id') is-invalid @enderror" readonly>
                    @error('order_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <hr>
                <h6 class="text-center text-primary">Data Siswa</h6>
                <hr>
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="nama" value="{{ old('nama', $spmb->nama) }}" class="form-control @error('nama') is-invalid @enderror">
                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $spmb->tempat_lahir) }}" class="form-control @error('tempat_lahir') is-invalid @enderror">
                        @error('tempat_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $spmb->tanggal_lahir) }}" class="form-control @error('tanggal_lahir') is-invalid @enderror">
                        @error('tanggal_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                        <option value="">-- Pilih --</option>
                        <option value="Laki-laki" {{ old('jenis_kelamin', $spmb->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin', $spmb->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Agama</label>
                        <input type="text" name="agama" value="{{ old('agama', $spmb->agama) }}" class="form-control @error('agama') is-invalid @enderror">
                        @error('agama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Suku</label>
                        <input type="text" name="suku" value="{{ old('suku', $spmb->suku) }}" class="form-control @error('suku') is-invalid @enderror">
                        @error('suku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>



                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $spmb->alamat) }}</textarea>
                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <hr>
                <h6 class="text-center text-primary">Data Sekolah Asal</h6>
                <hr>
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Nama Sekolah Asal</label>
                        <input type="text" name="nama_asal_sekolah" value="{{ old('nama_asal_sekolah', $spmb->nama_asal_sekolah) }}" class="form-control">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Alamat Sekolah</label>
                        <input type="text" name="alamat_sekolah" value="{{ old('alamat_sekolah', $spmb->alamat_sekolah) }}" class="form-control">
                    </div>
                </div>
                <hr>
                <h6 class="text-center text-primary">Data Orang Tua</h6>
                <hr>
                 <div class="mb-3">
                    <label class="form-label">Select Data</label>
                    <select name="select_data" class="form-select @error('select_data') is-invalid @enderror">
                        <option value="orang_tua" {{ old('select_data', $spmb->select_data) == 'orang_tua' ? 'selected' : '' }}>Orang Tua</option>
                        <option value="wali" {{ old('select_data', $spmb->select_data) == 'wali' ? 'selected' : '' }}>Wali</option>
                    </select>
                    @error('select_data') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Nama Ayah</label>
                        <input type="text" name="nama_ayah" value="{{ old('nama_ayah', $spmb->nama_ayah) }}" class="form-control">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Pendidikan Ayah</label>
                        <input type="text" name="pendidikan_ayah" value="{{ old('pendidikan_ayah', $spmb->pendidikan_ayah) }}" class="form-control">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Pekerjaan Ayah</label>
                        <input type="text" name="pekerjaan_ayah" value="{{ old('pekerjaan_ayah', $spmb->pekerjaan_ayah) }}" class="form-control">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Alamat Ayah</label>
                        <input type="text" name="alamat_ayah" value="{{ old('alamat_ayah', $spmb->alamat_ayah) }}" class="form-control">
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Nama Ibu</label>
                        <input type="text" name="nama_ibu" value="{{ old('nama_ibu', $spmb->nama_ibu) }}" class="form-control">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Pendidikan Ibu</label>
                        <input type="text" name="pendidikan_ibu" value="{{ old('pendidikan_ibu', $spmb->pendidikan_ibu) }}" class="form-control">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Pekerjaan Ibu</label>
                        <input type="text" name="pekerjaan_ibu" value="{{ old('pekerjaan_ibu', $spmb->pekerjaan_ibu) }}" class="form-control">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Alamat Ibu</label>
                        <input type="text" name="alamat_ibu" value="{{ old('alamat_ibu', $spmb->alamat_ibu) }}" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Nama Wali</label>
                        <input type="text" name="nama_wali" value="{{ old('nama_wali', $spmb->nama_wali) }}" class="form-control">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Pekerjaan Wali</label>
                        <input type="text" name="pekerjaan_wali" value="{{ old('pekerjaan_wali', $spmb->pekerjaan_wali) }}" class="form-control">
                    </div>
                    <div class="mb-3 col-md-12">
                        <label for="phone">Nomor Hp</label>
                        <input type="text" name="phone" value="{{ old('phone', $spmb->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3 col-md-12">
                        <label class="form-label">Alamat Wali</label>
                        <input type="text" name="alamat_wali" value="{{ old('alamat_wali', $spmb->alamat_wali) }}" class="form-control">
                    </div>
                </div>
                 <hr>
                <h6 class="text-center text-primary">Data Pendukung</h6>
                <hr>
                <div class="row">
                    <div class="mb-3 col-md-3">
                        <label class="form-label">File STTB</label><br>
                        @if($spmb->file_sttb)
                            <a href="{{ asset('storage/files/spmb/file_sttb/'.$spmb->file_sttb) }}" target="_blank">Preview File</a>
                            <a href="{{ asset('storage/files/spmb/file_sttb/'.$spmb->file_sttb) }}" download>Download File</a>
                        @else
                            <a href="#"><i class="fa fa-solid fa-file-circle-xmark"></i> Tidak Ada File</a>
                        @endif
                        <input type="file" name="file_sttb" class="form-control">
                    </div>
                    <div class="mb-3 col-md-3">
                        <label class="form-label">Akta Kelahiran</label><br>
                        @if($spmb->akta_kelahiran)
                            <a href="{{ asset('storage/files/spmb/akta_kelahiran/'.$spmb->akta_kelahiran) }}" target="_blank"><i class="fa fa-eye"></i> Preview File</a>
                            |
                            <a href="{{ asset('storage/files/spmb/akta_kelahiran/'.$spmb->akta_kelahiran) }}" download><i class="fa fa-download"></i> Download File</a>
                        @else
                            <a href="#"><i class="fa fa-solid fa-file-circle-xmark"></i> Tidak Ada File</a>
                        @endif
                        <input type="file" name="akta_kelahiran" class="form-control" value="{{ old('akta_kelahiran', $spmb->akta_kelahiran) }}">
                    </div>
                    <div class="mb-3 col-md-3">
                        <label class="form-label">Kartu Keluarga</label><br>
                        @if($spmb->kk)
                            <a href="{{ asset('storage/files/spmb/kk/'.$spmb->kk) }}" target="_blank"><i class="fa fa-eye"></i> Preview File</a>
                            |
                            <a href="{{ asset('storage/files/spmb/kk/'.$spmb->kk) }}" download><i class="fa fa-download"></i> Download File</a>
                        @else
                            <a href="#"><i class="fa fa-solid fa-file-circle-xmark"></i> Tidak Ada File</a>
                        @endif
                        <input type="file" name="kk" class="form-control" value="{{ old('kk', $spmb->kk) }}">
                    </div>
                    <div class="mb-3 col-md-3">
                        <label class="form-label">Pas Foto</label><br>
                        @if($spmb->pas_foto)
                            <a href="{{ asset('storage/files/spmb/pas_foto/'.$spmb->pas_foto) }}" target="_blank" ><i class="fa fa-eye"></i> Preview File</a>
                            |
                            <a href="{{ asset('storage/files/spmb/pas_foto/'.$spmb->pas_foto) }}" download><i class="fa fa-download"></i> Download File</a>
                        @else
                            <a href="#" class="mb-2 d-block">Tidak Ada File</a>
                        @endif
                        <input type="file" name="pas_foto" class="form-control" value="{{ old('pas_foto', $spmb->pas_foto) }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Status SPMB</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="pending" {{ old('status', $spmb->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="diterima" {{ old('status', $spmb->status) == 'diterima' ? 'selected' : '' }}>Diterima</option>
                            <option value="tidak_diterima" {{ old('status', $spmb->status) == 'tidak_diterima' ? 'selected' : '' }}>Tidak Diterima</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 col-md-12">
                         <a href="{{ route('dashboard.spmb.index') }}" class="mt-3 btn btn-danger btn-sm">Kembali</a>
                         <button type="submit" class="mt-3 btn btn-primary btn-sm float-end">Simpan Perubahan</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
