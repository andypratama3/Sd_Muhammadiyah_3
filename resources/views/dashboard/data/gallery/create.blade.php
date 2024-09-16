@extends('layouts.dashboard')
@section('title', 'Tambah Fasilitas')
@section('content')
    <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Aktivitas Gallery</h6>
        </div>
        <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.gallery.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group mt-2">
            <label for="name">Nama</label>
            <input type="text" class="form-control" name="name" id="name" aria-describedby="name" placeholder="Masukan Nama">
            </div>
            <div class="form-group mt-2">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" name="foto">
                </div>
            </div>
            <div class="form-group mt-2">
                <a href="{{ route('dashboard.datasekolah.gallery.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
            <button type="submit"  class="btn btn-primary float-lg-right">Submit</button>
        </div>
        </form>
        </div>
    </div>
@endsection
