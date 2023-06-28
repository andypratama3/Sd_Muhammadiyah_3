@extends('layouts.dashboard')
@section('title', 'Tambah Berita')
@section('content')
    <div class="card mb-4">
        <div class="card-body">
        <form action="{{ route('dashboard.berita.update',$berita->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
            <label for="judul">Judul</label>
            <input type="text" class="form-control" name="judul" id="judul" value="{{ $berita->judul }}" aria-describedby="emailHelp" placeholder="Masukan Judul">
            </div>
            <div class="form-group">
            <label for="">Deskripsi</label>
            <input type="text" class="form-control" id="" value="{{ $berita->desc }}" name="desc" placeholder="Deskripsi">
            </div>
            <div class="form-group">
            <label for="">Foto</label>
            <div class="custom-file">
                <input type="file" class="custom-file-input" name="foto" id="customFile" value="{{ $berita->foto }}">
                <label class="custom-file-label" for="customFile">Choose file</label>
            </div>
            </div>
            <button type="submit" class="btn btn-primary float-end">Submit</button>
        </form>
        </div>
    </div>
@endsection
