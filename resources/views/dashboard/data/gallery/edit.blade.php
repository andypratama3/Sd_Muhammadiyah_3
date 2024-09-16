@extends('layouts.dashboard')
@section('title', 'Edit gallery')
@section('content')
<div class="card mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Edit Gallery {{ $gallery->name }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.gallery.update',$gallery->slug) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $gallery->slug }}">
            <div class="form-group mt-2">
                <label for="name">Nama gallery</label>
                <input type="text" class="form-control" name="name" id="name" value="{{ $gallery->name }}"
                    aria-describedby="emailHelp" placeholder="Masukan Nama">
            </div>
            <div class="form-group mt-2">
                <input type="file" name="foto" class="form-control mb-2 text-center" value="{{ $gallery->foto }}" onchange="document.getElementById('output').src = window.URL.createObjectURL(this.files[0])">
            </div>
            <div class="form-group mt-2 text-center mb-4">
                <p>Foto Yang Di Gunakan</p>
                <img src="{{ asset('storage/img/gallery/'. $gallery->foto) }}" alt="" style="width: 100%;" id="output">
            </div>
            <a href="{{ route('dashboard.datasekolah.gallery.index') }}" class="btn btn-danger btn-sm">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-end btn-sm">Submit</button>
        </form>
    </div>
</div>

@endsection
