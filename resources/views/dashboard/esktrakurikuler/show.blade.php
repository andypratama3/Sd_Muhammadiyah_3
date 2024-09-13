@extends('layouts.dashboard')
@section('title', 'Detail ekstrakurikuler')
@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="form-group mt-2">
            <label for="name">Nama</label>
            <input type="text" class="form-control" id="name" aria-describedby="emailHelp"
                value="{{ $ekstrakurikuler->name }}" readonly>
        </div>
        <div class="form-group mt-2">
            <label for="">Deskripsi</label>
            <input type="text" class="form-control" id="" value="{{ $ekstrakurikuler->desc }}" readonly>
        </div>
        <div class="form-group mt-2 mb-2">
            <h6 class="text-center">Foto</h6>
            <div class="form-group">
                @php
                    $images = explode(',',$ekstrakurikuler->foto);
                    $imgs = reset($images);
                @endphp
                 <div class="form-group text-center">
                    <div class="" style="grid-template-columns: 60px 60px;">
                    @foreach ($images as $image => $i)
                        <img src="{{ asset('storage/ekstrakurikuler/'. trim($i)) }}" alt="" srcset="" style="width: 40%; height: 100%; margin-bottom: 40px;">
                    @endforeach
                    </div>
                </div>
            </div>
            <a href="{{ route('dashboard.datasekolah.ekstrakurikuler.index') }}" class="btn btn-danger btn-sm float-end">Kembali</a>
        </div>
    </div>
</div>
@endsection
