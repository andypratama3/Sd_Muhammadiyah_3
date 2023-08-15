@extends('layouts.dashboard')
@section('title', 'Tambah Artikel')
@section('content')
@push('css')
    <link href="{{ asset('asset_dashboard/vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('asset_dashboard/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
@endpush
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Artikel</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.berita.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="judul">Judul</label>
                <input type="text" class="form-control" name="judul" id="judul" aria-describedby="emailHelp"
                    placeholder="Masukan Judul">
            </div>
            <div class="form-group">
                    <div class="quill-editor-full">
                      <p>Hello World!</p>
                      <p>This is Quill <strong>full</strong> editor</p>
                    </div>

            </div>
            <a href="{{ route('dashboard.artikel.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
        </form>
    </div>
</div>
@push('js')
<script src="{{ asset('asset_dashboard/vendor/quill/quill.min.js') }}"></script>
<script src="{{ asset('asset_dashboard/vendor/quill/main.js') }}"></script>

@endpush
@endsection
