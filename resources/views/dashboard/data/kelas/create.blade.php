@extends('layouts.dashboard')
@section('title', 'Tambah Kelas')
@section('content')
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Kelas</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.kelas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Nama Kelas --}}
            <div class="form-group mt-2">
                <label for="Kelas">Nama Kelas</label>
                <input type="text" class="form-control" name="name" id="Kelas" placeholder="Masukan Nama Kelas">
            </div>

            {{-- Kategori Kelas --}}
            <div class="form-group mt-3">
                <label>Kategori Kelas</label>
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
                        <tr>
                            <td>
                                <input type="text" class="form-control" name="category_kelas[]" placeholder="Masukkan Kategori Kelas">
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pelajaran --}}
            <div class="form-group mt-3 mb-3">
                <label>Pelajaran</label>
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
                        <tr>
                            <td>
                                <select name="pelajaran[]" class="form-control select2">
                                    <option value="">-- Pilih Pelajaran --</option>
                                    @foreach ($pelajarans as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td></td>
                        </tr>
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

    // Init select2 pada row pertama yang sudah ada
    initSelect2('select[name="pelajaran[]"]');

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
        // Init select2 pada row yang baru ditambahkan
        initSelect2(newRow.find('select'));
    });

    $(document).on('click', '.remove-pelajaran', function () {
        // Destroy select2 sebelum remove agar tidak memory leak
        $(this).closest('tr').find('select').select2('destroy');
        $(this).closest('tr').remove();
    });

});
</script>
@endpush
@endsection