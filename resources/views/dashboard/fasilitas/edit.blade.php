@extends('layouts.dashboard')
@section('title', 'Edit fasilitas')
@section('content')
    <div class="card mb-4">
        <div class="card-body">
        <form action="{{ route('dashboard.fasilitas.update',$fasilitas->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
            <label for="judul">Nama Fasilitas</label>
            <input type="text" class="form-control" name="judul" id="judul" value="{{ $fasilitas->nama_fasilitas }}" aria-describedby="emailHelp" placeholder="Masukan Judul">
            </div>
            <div class="form-group">
            <label for="">Deskripsi</label>
            <input type="text" class="form-control" id="" value="{{ $fasilitas->desc }}" name="desc" placeholder="Deskripsi">
            </div>
            <div class="form-group">
            <label for="">Foto</label>
            <div class="custom-file">
                <input type="file" class="custom-file-input" name="foto" id="customFile" value="{{ $fasilitas->foto }}">
                <label class="custom-file-label" for="customFile">Choose file</label>
            </div>
            </div>
            <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
        </form>
        </div>
    </div>
@endsection
