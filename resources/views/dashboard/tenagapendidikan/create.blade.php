
@extends('layouts.dashboard')
@section('title', 'Tenaga Pendidikan')
@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')
    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Tenaga Pendidikan</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.tenagapendidikan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mt-2 form-group">
                <label for="judul">Nama</label>
                <input type="text" class="form-control" name="name" id="name"  placeholder="Masukan nama">
            </div>
            <div class="mt-2 form-group">
                <label for="">Jabatan</label>
                <input type="text" class="form-control" name="jabatan" placeholder="jabatan">
            </div>
            <div class="mt-2 form-group">
                <label for="">Struktur Tenaga Pendidikan</label>
                <select name="struktur_tenaga_pendidikan_id" id="struktur_tenaga_pendidikan_id" class="form-control select2">
                    <option value="" selected>Pilih Struktur</option>
                    @foreach ($strukturTenagaPendidikan as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-2 form-group">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*" onchange="document.getElementById('output').src = window.URL.createObjectURL(this.files[0])">
                </div>
                <div class="mt-3 text-center">
                    <h6 class="">Poto yang di pilih</h6>
                    <img src="" id="output" alt="" style="width: 200px; height: 50%;">
                </div>
            </div>
            <a href="{{ route('dashboard.datasekolah.tenagapendidikan.index') }}" class="btn btn-danger btn-sm float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary btn-sm float-lg-end">Simpan</button>
        </form>
    </div>
</div>

@endsection
