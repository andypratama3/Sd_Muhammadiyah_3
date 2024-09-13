@extends('layouts.dashboard')
@section('title', 'Detail Berita')
@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.guru.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group mt-2">
                <label for="name">Nama</label>
                <input type="text" class="form-control" id="name" aria-describedby="emailHelp"
                    value="{{ $berita->name }}" readonly>
            </div>
            <div class="form-group mt-2">
                <label for="">Deskripsi</label>
                <input type="text" class="form-control" id="" value="{{ $berita->desc }}" readonly>
            </div>
            <div class="form-group mt-2 mb-2">
                <img src="{{ asset('storage/img/berita/'.$berita->foto) }}" alt="" srcset=""
                    style="width: 100%; height:">
            </div>
            <a href="{{ route('dashboard.datasekolah.guru.index') }}" class="btn btn-danger float-end">Kembali</a>
        </form>
    </div>
</div>
@endsection
