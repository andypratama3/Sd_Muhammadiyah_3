@extends('layouts.dashboard')
@section('title', 'Edit Struktur Tenaga Pendidikan')
@section('content')
<div class="mb-4 card">
    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Edit Struktur Tenaga Pendidikan</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.struktur.tenaga.pendidikan.update', $struktur->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label" for="name">Nama Struktur</label>
                <input type="text" class="form-control" name="name" id="name" aria-describedby="Struktur "
                    value="{{ $struktur->name }}" placeholder="Masukan Nama Struktur">
            </div>
            <div class="mt-2 form-group">
                <label class="form-label" for="struktur_tenaga_pendidikan_id">Struktur Tenaga Pendidikan</label>
                <select name="struktur_tenaga_pendidikan_id" id="struktur_tenaga_pendidikan" class="form-control select2">
                    <option value="" selected>Pilih Struktur</option>
                    @foreach ($strukturTenagaPendidikan as $item)
                        <option value="{{ $item->id }}" {{ $struktur->struktur_tenaga_pendidikan_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-2 form-group">
                <a href="{{ route('dashboard.datasekolah.struktur.tenaga.pendidikan.index') }}" class="btn btn-sm btn-danger float-lg-start">Kembali</a>
                <button type="submit" class="btn btn-sm btn-primary float-lg-end">Submit</button>
            </div>
        </form>
    </div>
</div>
@endsection

