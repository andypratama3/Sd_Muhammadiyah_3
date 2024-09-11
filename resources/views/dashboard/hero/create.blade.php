@extends('layouts.dashboard')
@section('title', 'Tambah Gambar Depan')
@section('content')
<div class="card mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Gambar Depan</h6>
    </div>
    <div class="card-body">
        @include('layouts.flashmessage')
        <form action="{{ route('dashboard.news.hero.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group mt-2">
                <label for="name">Nama</label>
                <input type="text" class="form-control" name="name" id="name" aria-describedby="fasilitas"
                    placeholder="Masukan Nama Hero" value="{{ old('name') }}">
            </div>
            <div class="form-group mt-2">
                <label for="">Deskripsi Singkat</label>
                <input type="text" class="form-control" id="" name="desc" placeholder="Deskripsi"
                    value="{{ old('desc') }}">
            </div>
            <div class="form-group mt-2">
                <label for="">Link</label>
                <input type="text" class="form-control" id="" name="link" placeholder="Link Url"
                    value="{{ old('link') }}">
            </div>
            <div class="form-group mt-2">
                <label for="">Youtube</label>
                <input type="text" class="form-control" id="" name="youtube" placeholder="Link Youtube"
                    value="{{ old('youtube') }}">
            </div>

            <div class="form-group mt-2">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,application/pdf,image" value="{{ old('foto') }}" onchange="document.getElementById('output').src = window.URL.createObjectURL(this.files[0])">
                </div>

                <div class="mt-4 text-center">
                    <img src="" id="output" alt="" style="width: 100%;">
                </div>
            </div>
            <div class="form-group mt-4">
                <a href="{{ route('dashboard.news.hero.index') }}" class="btn btn-danger float-lg-start btn-sm">Kembali</a>
                <button type="submit" class="btn btn-primary float-lg-end btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>


@endsection
