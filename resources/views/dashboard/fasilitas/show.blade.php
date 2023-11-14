@extends('layouts.dashboard')
@section('title', 'Detail fasilitas')
@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <div class="form-group">
            <label for="judul">Nama</label>
            <input type="text" class="form-control" id="judul" aria-describedby="emailHelp" value="{{ $fasilitas->nama_fasilitas }}" readonly>
            </div>
            <div class="form-group">
            <label for="">Deskripsi</label>
            <input type="text" class="form-control" id="" value="{{ $fasilitas->desc }}" readonly>
            </div>
            <div class="form-group">
                <input type="text" readonly value="Foto" class="form-control mb-2 text-center">
                <div class="" style="grid-template-columns: 60px 60px;">
                @foreach ($images as $image)
                <img src="{{ asset('storage/img/fasilitas/'.$image) }}" alt="" srcset="" style="width: 40%; height: 100%; margin-bottom: 40px;">
                @endforeach
                </div>
            </div>
            <a href="{{ route('dashboard.fasilitas.index') }}" class="btn btn-danger float-end">Kembali</a>
        </div>
    </div>
@endsection
