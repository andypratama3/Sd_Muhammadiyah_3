@extends('layouts.dashboard')
@section('title','Tambah Role')

@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css">
@endpush

@section('content')

<div class="row">
    <div class="col-lg-12">
        <!-- Form Basic -->
        <div class="card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary text-center">Tambah Role</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('dashboard.pengaturan.role.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="name">Nama Role</label>
                        <input type="text" class="form-control" id="name" aria-describedby="role" name="name"
                            placeholder="Masukan Nama Role">
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <table class="table table-bordered table-striped" border="10"
                                    style=" text-align:center;">
                                    <thead>
                                        <tr>
                                            <th scope="col" rowspan="2" class="text-center"
                                                style="vertical-align:middle">Tugas</th>
                                            <th scope="col" colspan="5" class="text-center">Hak Akses</th>
                                        </tr>
                                        <tr>
                                            <th scope="col" class="text-center">
                                                Pilih Semua
                                            </th>
                                            <th scope="col" class="text-center">
                                                Tambah
                                            </th>
                                            <th scope="col" class="text-center">
                                                Hapus
                                            </th>
                                            <th scope="col" class="text-center">
                                                Edit
                                            </th>
                                            <th scope="col" class="text-center">
                                                Lihat
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tasks as $task)
                                        <tr>
                                            <td scope="row">{{ $task->description }}</td>
                                            <th scope="col" class="text-center">
                                                <input type="checkbox" name="izin" value="{{ $task->slug }}"
                                                    class="checkAll checkAll{{ $task->slug }}" />
                                            </th>
                                            @foreach ($task->permissions as $permission)
                                            <td class="{{ $task->slug }}">
                                                <div class=" hak{{ $task->slug }}">
                                                    <input type="checkbox" name="izin_akses[]"
                                                        value="{{ $permission->id }}"
                                                        class="check{{ $task->slug }} hakakses"
                                                        id="{{ $permission->name }}" />
                                                </div>
                                            </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@push('js')
<!-- Select2 -->
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function () {
        // Select2 Multiple
        $('.select2-multiple').select2();
        var id = $(this).data('id');
        $('#id').attr('value', id);


    });
</script>
@endpush
