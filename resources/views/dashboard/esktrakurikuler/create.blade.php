@extends('layouts.dashboard')
@section('title', 'Tambah Esktrakurikuler')
@section('content')
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Esktrakurikuler</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.ekstrakurikuler.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="judul">Nama</label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Masukan name" value="{{ old('name') }}">
            </div>
            <div class="form-group">
                <label for="">Deskripsi</label>
                <input type="text" class="form-control" id="" name="desc" value="{{ old('desc') }}" placeholder="Deskripsi">
            </div>
            <div class="form-group">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" multiple name="foto[]">
                </div>
                <div class="mt-3 text-center">
                    <h6 class="">Poto yang di pilih</h6>
                    <img src="" id="output" alt="" style="width: 200px; height: 50%;">
                </div>
            </div>
            <a  href="{{ route('dashboard.datasekolah.ekstrakurikuler.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
        </form>
    </div>
</div>
@endsection
