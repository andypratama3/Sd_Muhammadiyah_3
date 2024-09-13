@extends('layouts.dashboard')
@section('title', 'Edit fasilitas')
@section('content')
    <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Edit Fasilitas {{ $fasilitas->name }}</h6>
            </div>
        <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.fasilitas.update',$fasilitas->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $fasilitas->slug }}">
            <div class="form-group">
            <label for="nama_fasilitas">Nama Fasilitas</label>
                <input type="text" class="form-control" name="nama_fasilitas" id="nama_fasilitas" value="{{ $fasilitas->nama_fasilitas }}" aria-describedby="emailHelp" placeholder="Masukan nama_fasilitas">
            </div>
            <div class="form-group mb-2">
            <label for="">Deskripsi</label>
                <input type="text" class="form-control" id="" value="{{ $fasilitas->desc }}" name="desc" placeholder="Deskripsi">
            </div>
            @php
            $fasilitas_foto = explode(',',$fasilitas->foto);
            $firstCover = reset($fasilitas_foto);
            @endphp
            <div class="form-group">
                <input type="file" name="foto[]" class="form-control mb-2 text-center" multiple>
                <div class="" style="grid-template-columns: 60px 60px;">
                @foreach ($fasilitas_foto as $image => $i)
                    <img src="{{ asset('storage/img/fasilitas/'. trim($i)) }}" alt="" srcset="" style="width: 40%; height: 100%; margin-bottom: 40px;">
                @endforeach
                </div>
            </div>
            <a href="{{ route('dashboard.datasekolah.fasilitas.index') }}" class="btn btn-danger btn-sm">Kembali</a>
            <button type="submit" class="btn btn-sm btn-primary float-lg-end">Submit</button>
        </form>
        </div>
    </div>

@endsection
