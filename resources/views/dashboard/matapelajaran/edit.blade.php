@extends('layouts.dashboard')

@section('title', 'Edit Mata Pelajaran')

@section('content')
<div class="mb-4 card">
    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Pelajaran</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.matapelajaran.update', $matapelajaran->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $matapelajaran->slug }}">
            <div class="form-group">
                <label for="name">Mata Pelajaran</label>
                <input type="text" class="form-control" name="name" id="name" aria-describedby="pelajaran"
                    value="{{ $matapelajaran->name }}"
                    placeholder="Masukan Nama pelajaran">
            </div>
            <div class="mt-2 form-group">
                <a href="{{ route('dashboard.datasekolah.matapelajaran.index') }}" class="btn btn-sm btn-danger float-lg-start">Kembali</a>
                <button type="submit" class="btn btn-sm btn-primary float-lg-end">Submit</button>
            </div>
        </form>
    </div>
</div>
@endsection
