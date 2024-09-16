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
                            <label>Pilih Hak Akses <code>*</code></label>
                            @if ($errors->has('permissions'))
                            <div class="row">
                                <div class="text-danger">{{ $errors->first('permissions') }}</div>
                            </div>
                            @endif
                            <table class="table table-bordered table-striped mb-5">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center" style="vertical-align:middle">Tugas</th>
                                        <th scope="col" colspan="5" class="text-center">Hak Akses</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tasks as $task)
                                    <tr>
                                        <td scope="row">{{ $task->description }}</td>
                                        <th scope="col" class="text-center">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input {{ $errors->has('permissions') ? 'is-invalid' : '' }} checkAll checkAll{{ $task->slug }}" id="checkAllCustom{{ $task->slug }}" name="izin" value="{{ $task->slug }}">
                                                <label for="checkAllCustom{{ $task->slug }}" class="form-check-label custom-control-label">Pilih Semua</label>
                                            </div>
                                        </th>
                                        @foreach ($task->permissions as $permission)
                                        <td class="{{ $task->slug }}">
                                            <div class=" hak{{ $task->slug }}">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input {{ $errors->has('permissions') ? 'is-invalid' : '' }} check{{ $task->slug }} hakakses" id="{{ $permission->name }}" name="permissions[]" value="{{ $permission->id }}">
                                                    <label for="{{ $permission->name }}" class="form-check-label custom-control-label">{{ explode(' ', $permission->name)[0] }}</label>
                                                </div>
                                            </div>
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary float-lg-end btn-sm">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<!-- Select2 -->
<script>
    $(document).ready(function () {
        $("#nama").keypress(function () {
            $("#nama").removeClass("is-invalid");
            $("#textNama").html("");
        });
        // Select2 Multiple
        $('.checkAll').on('change', function (){
            if($(this).is(':checked')) {
                $(".check" + this.value).prop('checked', true);
            } else {
                $(".check" + this.value).prop('checked', false);
            }
        });
        $(".hakakses").on('click', function () {
            var header = $(this).attr('class');
            var classParent = header.replace(" hakakses", "");
            var countChecked = $('.' + classParent + ':checked').length;
            var parentClass = $(this).closest('td').attr('class');
            if (countChecked == 4) {
                $(".checkAll" + parentClass).prop('checked', true);
            } else {
                $(".checkAll" + parentClass).prop('checked', false);
            }
        });

    });
</script>
@endpush
