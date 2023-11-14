@extends('layouts.dashboard')
@section('title', 'Tambah Fasilitas')
@section('content')
    <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Fasilitas</h6>
        </div>
        <div class="card-body">
        <form action="{{ route('dashboard.fasilitas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
            <label for="nama_fasilitas">Fasilitas</label>
            <input type="text" class="form-control" name="nama_fasilitas" id="nama_fasilitas" aria-describedby="emailHelp" placeholder="Masukan Nama Fasilitas">
            </div>
            <div class="form-group">
            <label for="">Deskripsi</label>
            <input type="text" class="form-control" id="" name="desc" placeholder="Deskripsi">
            </div>
            <div class="form-group">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" multiple id="foto" name="foto" accept="image/*" onchange="document.getElementById('output').src = window.URL.createObjectURL(this.files[0])">
                </div>
                <div class="mt-3 text-center">
                    <h6 class="">Poto yang di pilih</h6>
                    <img src="" id="output" alt="" style="width: 200px; height: 50%;">
                </div>
            </div>
            <div class="form-group">
                <a href="{{ route('dashboard.fasilitas.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
            <button type="submit"  class="btn btn-primary float-lg-right">Submit</button>
        </div>
        </form>
        </div>
    </div>
@endsection
