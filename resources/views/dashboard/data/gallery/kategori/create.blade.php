@extends('layouts.dashboard')
@section('title', 'Tambah Kategori Gallery')
@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')
    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Kategori Gallery</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.kategori.gallery.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mt-2 form-group">
                <label class="form-label" for="name">Nama</label>
                <input type="text" class="form-control" name="name" id="name" aria-describedby="name"
                    placeholder="Masukan Nama">
            </div>
            <div class="mt-2 mb-2 form-group">
                <a href="{{ route('dashboard.datasekolah.kategori.gallery.index') }}" class="btn btn-danger btn-sm float-lg-start">Kembali</a>
                <button type="submit" class="btn btn-primary btn-sm float-lg-end">Submit</button>
            </div>
        </form>
    </div>
</div>
@endsection
