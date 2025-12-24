@extends('layouts.dashboard')
@section('title', 'Edit Fasilitas')

@section('content')
<div class="mb-4 card">
    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold">Edit Fasilitas</h6>
    </div>

    <div class="card-body">
        @include('layouts.flashmessage')

        <form
            action="{{ route('dashboard.datasekolah.fasilitas.update', $fasilitas->slug) }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PUT')

            <input type="hidden" name="slug" value="{{ $fasilitas->slug }}">

            {{-- Nama Fasilitas --}}
            <div class="mt-2 form-group">
                <label class="form-label">Fasilitas</label>
                <input type="text" name="nama_fasilitas"
                    class="form-control @error('nama_fasilitas') is-invalid @enderror"
                    value="{{ old('nama_fasilitas', $fasilitas->nama_fasilitas) }}">
                @error('nama_fasilitas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ukuran --}}
            <div class="mt-2 form-group">
                <label class="form-label">Ukuran</label>
                <input type="text" name="ukuran"
                    class="form-control @error('ukuran') is-invalid @enderror"
                    value="{{ old('ukuran', $fasilitas->ukuran) }}">
                @error('ukuran')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Kapasitas --}}
            <div class="mt-2 form-group">
                <label class="form-label">Kapasitas</label>
                <input type="number" name="kapasitas"
                    class="form-control @error('kapasitas') is-invalid @enderror"
                    value="{{ old('kapasitas', $fasilitas->kapasitas) }}">
                @error('kapasitas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="mt-2 form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="desc" rows="4"
                    class="form-control @error('desc') is-invalid @enderror">{{ old('desc', $fasilitas->desc) }}</textarea>
                @error('desc')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Foto --}}
            <div class="mt-2 form-group">
                <label class="form-label">Foto (Opsional)</label>
                <input type="file" name="foto[]" class="form-control" multiple>

                {{-- preview foto lama --}}
                @if($fasilitas->foto)
                    <div class="mt-2">
                        @foreach(explode(',', $fasilitas->foto) as $foto)
                            <img src="{{ asset('storage/img/fasilitas/'.$foto) }}"
                                width="50%" class="mb-2 rounded me-2">
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Kelengkapan --}}
            <div class="mt-4 form-group">
                <label class="form-label">Kelengkapan Fasilitas</label>

                <button type="button"
                    class="mt-2 float-end btn btn-sm btn-success"
                    id="add-row">
                    + Tambah Kelengkapan
                </button>

                <table class="table mt-3">
                    <thead>
                        <tr>
                            <th>Nama Kelengkapan</th>
                            <th width="50">#</th>
                        </tr>
                    </thead>

                    <tbody id="kelengkapan-wrapper">
                        @forelse ($fasilitas->kelengkapan as $item)
                            <tr>
                                <td>
                                    <input type="text" name="kelengkapan[]"
                                        class="form-control"
                                        value="{{ $item->nama }}">
                                </td>
                                <td>
                                    <button type="button"
                                        class="btn btn-sm btn-danger remove-row">X</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td>
                                    <input type="text" name="kelengkapan[]" class="form-control">
                                </td>
                                <td>
                                    <button type="button"
                                        class="btn btn-sm btn-danger remove-row">X</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Action --}}
            <div class="mt-4 form-group">
                <a href="{{ route('dashboard.datasekolah.fasilitas.index') }}"
                    class="btn btn-danger btn-sm">Kembali</a>
                <button type="submit"
                    class="btn btn-primary btn-sm float-end">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- JS --}}
<script>
document.getElementById('add-row').addEventListener('click', function () {
    document.getElementById('kelengkapan-wrapper')
        .insertAdjacentHTML('beforeend', `
        <tr>
            <td><input type="text" name="kelengkapan[]" class="form-control"></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-row">X</button></td>
        </tr>
    `)
})

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove()
    }
})
</script>
@endsection
