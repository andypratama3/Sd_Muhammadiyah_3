@extends('layouts.dashboard')
@section('title', 'Edit fasilitas')
@section('content')
    <div class="card mb-4">
        @include('layouts.flashmessage')
        <div class="card-body">
        <form action="{{ route('dashboard.fasilitas.update',$fasilitas->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
            <label for="nama_fasilitas">Nama Fasilitas</label>
                <input type="text" class="form-control" name="nama_fasilitas" id="nama_fasilitas" value="{{ $fasilitas->nama_fasilitas }}" aria-describedby="emailHelp" placeholder="Masukan nama_fasilitas">
            </div>
            <div class="form-group">
            <label for="">Deskripsi</label>
                <input type="text" class="form-control" id="" value="{{ $fasilitas->desc }}" name="desc" placeholder="Deskripsi">
            </div>
            <div class="form-group">
                <label for="">Foto</label>
                <div class="custom-file mb-5">
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*" onchange="loadPreview(this)" multiple>
                </div>
                <div class="" style="grid-template-columns: 60px 60px;">
                    @foreach ($images as $image)
                    <img src="{{ asset('storage/img/fasilitas/'.$image) }}" alt="" srcset="" style="width: 40%; height: 100%; margin-bottom: 40px;">
                    @endforeach
                    </div>
            </div>
            <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
        </form>
        </div>
    </div>


@endsection
