@extends('layouts.dashboard')
@section('title','Detail role')
@section('content')

<div class="row">
    <div class="col-lg-12">
        <!-- Form Basic -->
        <div class="card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary text-center">Detail role</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('dashboard.pengaturan.role.update', $role->slug) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="name">Nama role</label>
                        <input type="text" class="form-control" id="name" aria-describedby="role" name="name"
                            placeholder="Masukan Nama role" value="{{ $role->name }}">
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <table class="table table-bordered table-striped" border='10' style=" text-align:center;">
                                    <thead>
                                        <tr>
                                            <th scope="col" rowspan="2" class="text-center" style="vertical-align:middle">Tugas</th>
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
                                                                id="{{ $permission->slug }}"
                                                                {{ in_array($permission->name, $izin) ? 'checked' : '' }} />
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
                    <a href="{{ route('dashboard.pengaturan.role.index') }}" class="btn btn-danger">Kembali</a>
                    <button type="submit" class="btn btn-primary float-lg-right">Ubah Role</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function () {
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
        var arrayClassParent = $(".hakakses")
            .map(function () {
                var header = $(this).attr('class');
                return header.replace(" hakakses", "");
            }).toArray();
        var uniqueNames = [];
        $.each(arrayClassParent, function (i, el) {
            if ($.inArray(el, uniqueNames) === -1) uniqueNames.push(el);
        });
        $.each(uniqueNames, function (index, role) {
            var countChecked = $('.' + role + ':checked').length;
            var parentClass = $('.' + role).closest('td').attr('class');
            if (countChecked == 4) {
                console.log(parentClass);
                $(".checkAll" + parentClass).prop('checked', true);
            }
        });
    });
</script>
@endpush

@endsection
