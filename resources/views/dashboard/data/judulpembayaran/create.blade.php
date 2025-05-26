@extends('layouts.dashboard')
@section('title', 'Tambah Kategori')
@section('content')

<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Kategori Pembayaran</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datamaster.judul.pembayaran.store') }}" method="POST">
            @csrf
            <div class="form-group mt-2 mb-2">
                <label for="name">Nama Kategori Pembayaran</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" placeholder="Masukan name">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group mt-2 mb-2">
                <label for="kode">Kode Pembayaran</label>
                <input type="text" class="form-control @error('kode') is-invalid @enderror" name="kode" id="kode" placeholder="Masukan Kode Pembayaran">
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <a href="{{ route('dashboard.datamaster.judul.pembayaran.index') }}" class="btn btn-danger btn-sm float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary btn-sm float-lg-end">Submit</button>
        </form>
    </div>
</div>
@endsection
