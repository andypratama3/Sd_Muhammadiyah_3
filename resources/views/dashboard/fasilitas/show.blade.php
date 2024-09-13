@extends('layouts.dashboard')
@section('title', 'Detail fasilitas')
@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="form-group">
            <label for="judul">Nama</label>
            <input type="text" class="form-control" id="judul" aria-describedby="emailHelp"
                value="{{ $fasilitas->nama_fasilitas }}" readonly>
        </div>
        <div class="form-group mb-2">
            <label for="">Deskripsi</label>
            <input type="text" class="form-control" id="" value="{{ $fasilitas->desc }}" readonly>
        </div>
        @php
        $fasilitas_foto = explode(',',$fasilitas->foto);
        $firstCover = reset($fasilitas_foto);
        @endphp
        <div class="form-group text-center">
            <input type="text" readonly value="Foto" class="form-control mb-2 text-center">
            <div class="" style="grid-template-columns: 60px 60px;">
                @foreach ($fasilitas_foto as $image => $i)
                <img src="{{ asset('storage/img/fasilitas/'. trim($i)) }}" alt="" srcset=""
                    style="width: 40%; height: 100%; margin-bottom: 40px;">
                @endforeach
            </div>
        </div>
        <a href="{{ route('dashboard.datasekolah.fasilitas.index') }}" class="btn btn-danger btn-sm">Kembali</a>
    </div>
</div>
@endsection
