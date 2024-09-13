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
            <div class="form-group mt-2">
                <label for="Kelas">Nama Kelas</label>
                <input type="text" class="form-control" name="name" id="Kelas" aria-describedby="emailHelp"
                    placeholder="Masukan Nama Kelas">
            </div>
            <div class="form-group mt-2 mb-2">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered text-center" id="dynamicAddRemove">
                            <tr>
                                <th class="w-75">Kategori Kelas</th>
                            </tr>
                            <td><input type="text" class="form-control" name="category_kelas[]" placeholder="Masukkan Kategori Kelas"></td>
                            <th class="w-25"><button type="button" id="dynamic-ar" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button></th>
                        </table>
                    </div>
                </div>
            </div>
            <a  href="{{ route('dashboard.datasekolah.kelas.index') }}" class="btn btn-danger float-lg-start btn-sm">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-end btn-sm">Submit</button>
        </form>
    </div>
</div>
@push('js')
<script>
    $(document).ready(function () {
        var i = 5;
        $("#dynamic-ar").click(function () {
            ++i;
            $("#dynamicAddRemove").append(
                `<tr>
                    <td>
                        <input type="text" class="form-control" name="category_kelas[` + i + `]" placeholder="Masukkan Kategori Kelas">
                    </td>
                    <td colspan="2">
                        <button type="button" class="btn btn-sm btn-danger remove-input-field"><i class="fas fa-trash"></i></button></td>
                    </td>
                </tr>`
            );
        });
        $(document).on('click', '.remove-input-field', function () {
            $(this).parents('tr').remove();
            --i;
        });
    });
</script>
@endpush
@endsection
