@extends('layouts.dashboard')
@section('title', 'Edit Kategori')
@section('content')

<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Edit Kategori Pembayaran</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datamaster.judul.pembayaran.update', $judulPembayaran->slug) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $judulPembayaran->slug }}">
            <div class="form-group">
                <label for="name">Nama Kategori Pembayaran</label>
                <input type="text" class="form-control" name="name" value="{{ $judulPembayaran->name }}" placeholder="Masukan name">
            </div>
            <a href="{{ route('dashboard.datamaster.judul.pembayaran.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
        </form>
    </div>
</div>
@endsection
