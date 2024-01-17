@extends('layouts.dashboard')
@section('title', 'Tambah Pelajaran')
@section('content')
<div class="card mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Pelajaran</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.matapelajaran.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="name">Mata Pelajaran</label>
                <input type="text" class="form-control" name="name" id="name" aria-describedby="pelajaran"
                    placeholder="Masukan Nama pelajaran">
            </div>
            <div class="form-group">
                <a href="{{ route('dashboard.datasekolah.matapelajaran.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
                <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
            </div>
        </form>
    </div>
</div>
@endsection
