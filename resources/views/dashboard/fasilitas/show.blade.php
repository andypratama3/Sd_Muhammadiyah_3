@extends('layouts.dashboard')
@section('title', 'Detail fasilitas')
@section('content')
    <div class="card mb-4">
        <div class="card-body">
        <form action="{{ route('dashboard.fasilitas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
            <label for="judul">Judul</label>
            <input type="text" class="form-control" id="judul" aria-describedby="emailHelp" value="{{ $fasilitas->nama_fasilitas }}" readonly>
            </div>
            <div class="form-group">
            <label for="">Deskripsi</label>
            <input type="text" class="form-control" id="" value="{{ $fasilitas->desc }}" readonly>
            </div>
            <div class="form-group">
            <img src="{{ asset('storage/img/fasilitas/'.$fasilitas->foto) }}" alt="" srcset="" style="width: 100%; height:">
            </div>
            <a href="{{ route('dashboard.fasilitas.index') }}" class="btn btn-danger float-end">Kembali</a>
        </form>
        </div>
    </div>
@endsection
