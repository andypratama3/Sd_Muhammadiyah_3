@extends('layouts.dashboard')
@section('title', 'Detail Berita')
@section('content')
<div class="card mb-4">
    <div class="card-body">
        <a href="{{ route('dashboard.news.berita.index') }}" class="btn btn-danger btn-sm">Kembali</a>
        <div class="form-group mt-4">
            <label for="judul">Judul</label>
            <input type="text" class="form-control" id="judul" aria-describedby="emailHelp" value="{{ $berita->judul }}"
                readonly>
        </div>
        <div class="form-group mt-2">
            <label for="">Deskripsi</label>
            <input type="text" class="form-control" id="" value="{{ $berita->desc }}" readonly>
        </div>
        <div class="form-group mb-2 mt-2">
            <h6 class="text-center">Foto</h6>
            <img src="{{ asset('storage/img/berita/'.$berita->foto) }}" alt="" srcset="" class="img-fluid" style="border-radius: 10px;">
        </div>
        </form>
    </div>
</div>
@endsection
