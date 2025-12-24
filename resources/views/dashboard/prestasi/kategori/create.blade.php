@extends('layouts.dashboard')
@section('title', 'Tambah Prestasi')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush
@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')
    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Kategori Prestasi</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.kategori.prestasi.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mt-2 form-group">
                <label class="form-label" for="name">Nama</label>
                <input type="text" class="form-control" name="name" id="name" aria-describedby="name"
                    placeholder="Masukan Prestasi">
            </div>
            <div class="mt-2 mb-2 form-group">
                <a href="{{ route('dashboard.datasekolah.kategori.prestasi.index') }}" class="btn btn-danger btn-sm float-lg-start">Kembali</a>
                <button type="submit" class="btn btn-primary btn-sm float-lg-end">Submit</button>
            </div>
        </form>
    </div>
</div>
@endsection
