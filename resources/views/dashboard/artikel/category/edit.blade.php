@extends('layouts.dashboard')
@section('title', 'Tambah Kategori')
@section('content')

<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Edit Kategori</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.news.category.update', $category->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $category->slug }}">
            <div class="form-group">
                <label for="name">Nama Kategori</label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Masukan name" value="{{ $category->name }}">
            </div>
            <a href="{{ route('dashboard.news.category.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
        </form>
    </div>
</div>
@endsection
