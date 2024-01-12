@extends('layouts.dashboard')
@section('title', 'Detail ekstrakurikuler')
@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="form-group">
            <label for="name">Nama</label>
            <input type="text" class="form-control" id="name" aria-describedby="emailHelp"
                value="{{ $ekstrakurikuler->name }}" readonly>
        </div>
        <div class="form-group">
            <label for="">Deskripsi</label>
            <input type="text" class="form-control" id="" value="{{ $ekstrakurikuler->desc }}" readonly>
        </div>
        <div class="form-group">
            <h6 class="text-center">Foto</h6>
            <div class="form-group">
                @php
                    $images = explode(',',$ekstrakurikuler->foto);
                    $imgs = reset($images);
                @endphp
                @foreach ($images as $image)
                <div class="card" style="width: 18rem;">
                    <img src="{{ asset('storage/ekstrakurikuler/' . trim($image)) }}" class="card-img-top" alt="...">
                </div>
                @endforeach
                <img src="{{ asset('storage/img/ekstrakurikuler/'.$ekstrakurikuler->foto) }}" alt="" srcset=""
                    style="width: 100%; height:">
            </div>
            <a href="{{ route('dashboard.ekstrakurikuler.index') }}" class="btn btn-danger float-end">Kembali</a>
        </div>
    </div>
</div>
@endsection
