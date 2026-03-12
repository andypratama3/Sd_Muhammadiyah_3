@extends('layouts.dashboard')
@section('title', 'Edit Kelas')
@section('content')
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Edit Kelas</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.kelas.update', $kelas->slug) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $kelas->slug }}">

            {{-- Nama Kelas --}}
            <div class="form-group mt-2">
                <label for="Kelas">Nama Kelas</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                    name="name" id="Kelas" value="{{ old('name', $kelas->name) }}"
                    placeholder="Masukan Nama Kelas">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Kategori Kelas --}}
            <div class="form-group mt-3">
                <label>Kategori Kelas</label>
                @error('category_kelas')
                    <div class="text-danger small mb-1">{{ $message }}</div>
                @enderror
                <table class="table table-bordered" id="dynamicAddRemove">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th style="width:60px">
                                <button type="button" id="dynamic-ar" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $categoryKelas = is_array($kelas->category_kelas)
                                ? $kelas->category_kelas
                                : json_decode($kelas->category_kelas, true) ?? [];
                            sort($categoryKelas);
                        @endphp
                        @forelse ($categoryKelas as $category)
                            <tr>
                                <td>
                                    <input type="text" class="form-control" name="category_kelas[]"
                                        value="{{ old('category_kelas.' . $loop->index, $category) }}"
                                        placeholder="Masukkan Kategori Kelas">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-kategori">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td>
                                    <input type="text" class="form-control" name="category_kelas[]"
                                        placeholder="Masukkan Kategori Kelas">
                                </td>
                                <td></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pelajaran --}}
            <div class="form-group mt-3">
                <label>Pelajaran</label>
                @error('pelajaran')
                    <div class="text-danger small mb-1">{{ $message }}</div>
                @enderror
                <table class="table table-bordered" id="dynamicAddRemovePelajaran">
                    <thead>
                        <tr>
                            <th>Mata Pelajaran</th>
                            <th style="width:60px">
                                <button type="button" id="dynamic-ar-pelajaran" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kelas->kelasPelajaran as $pelajaran)
                            <tr>
                                <td>
                                    <select name="pelajaran[]" class="form-control">
                                        <option value="">-- Pilih Pelajaran --</option>
                                        @foreach ($pelajarans as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $item->id === $pelajaran->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-pelajaran">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td>
                                    <select name="pelajaran[]" class="form-control">
                                        <option value="">-- Pilih Pelajaran --</option>
                                        @foreach ($pelajarans as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <a href="{{ route('dashboard.datasekolah.kelas.index') }}" class="btn btn-danger btn-sm">Kembali</a>
                <button type="submit" class="btn btn-primary btn-sm float-end">Submit</button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
$(document).ready(function () {

    function initSelect2(el) {
        $(el).select2();
    }

    // Init select2 pada SEMUA row existing (dari database)
    $('select[name="pelajaran[]"]').each(function () {
        initSelect2(this);
    });

    var pelajaranOptions = `<option value="">-- Pilih Pelajaran --</option>` +
        @foreach ($pelajarans as $item)
            `<option value="{{ $item->id }}">{{ $item->name }}</option>` +
        @endforeach
        ``;

    $("#dynamic-ar").click(function () {
        $("#dynamicAddRemove tbody").append(`
            <tr>
                <td><input type="text" class="form-control" name="category_kelas[]" placeholder="Masukkan Kategori Kelas"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-kategori"><i class="fas fa-trash"></i></button></td>
            </tr>
        `);
    });

    $(document).on('click', '.remove-kategori', function () {
        $(this).closest('tr').remove();
    });

    $("#dynamic-ar-pelajaran").click(function () {
        var newRow = $(`
            <tr>
                <td>
                    <select name="pelajaran[]" class="form-control">
                        ${pelajaranOptions}
                    </select>
                </td>
                <td><button type="button" class="btn btn-sm btn-danger remove-pelajaran"><i class="fas fa-trash"></i></button></td>
            </tr>
        `);
        $("#dynamicAddRemovePelajaran tbody").append(newRow);
        initSelect2(newRow.find('select'));
    });

    $(document).on('click', '.remove-pelajaran', function () {
        $(this).closest('tr').find('select').select2('destroy');
        $(this).closest('tr').remove();
    });

});
</script>
@endpush
@endsection