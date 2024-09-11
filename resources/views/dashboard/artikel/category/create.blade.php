@extends('layouts.dashboard')
@section('title', 'Tambah Kategori')
@section('content')

<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold">Tambah Kategori</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.news.category.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group mt-2 mb-2">
                <label for="name">Nama Kategori</label>
                <input type="text" class="form-control" name="name" id="name" aria-describedby="emailHelp"
                    placeholder="Masukan Nama">
            </div>
            <a href="{{ route('dashboard.news.category.index') }}" class="btn btn-danger btn-sm">Kembali</a>
            <button type="submit" class="btn btn-primary btn-sm float-lg-end">Submit</button>
        </form>
    </div>
</div>
@endsection
