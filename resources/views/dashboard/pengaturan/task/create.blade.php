@extends('layouts.dashboard')
@section('title','Tambah Task')
@section('content')

<div class="row">
    <div class="col-lg-12">
      <!-- Form Basic -->
      <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
          <h6 class="m-0 font-weight-bold text-primary text-center">Tambah Task</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('dashboard.pengaturan.task.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
              <label for="name">Nama Task</label>
              <input type="text" class="form-control" id="name" aria-describedby="task" name="name" placeholder="Masukan Nama Task">
            </div>
            <div class="form-group">
              <label for="name">Deskripsi Task</label>
              <input type="text" class="form-control" id="name" aria-describedby="task" name="description" placeholder="Masukan Deskripsi Task">
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Hak Akses Default <code>*</code></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input {{ $errors->has('permissions') ? 'is-invalid' : '' }} checkAll" id="checkAllCustom">
                                    <label for="checkAllCustom" class="form-check-label custom-control-label">Pilih Semua</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input {{ $errors->has('permissions') ? 'is-invalid' : '' }} check" id="checkLihat" name="permissions[]" value="View">
                                    <label for="checkLihat" class="form-check-label custom-control-label">Lihat</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input {{ $errors->has('permissions') ? 'is-invalid' : '' }} check" id="checkTambah" name="permissions[]" value="Create">
                                    <label for="checkTambah" class="form-check-label custom-control-label">Tambah</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input {{ $errors->has('permissions') ? 'is-invalid' : '' }} check" id="checkUbah" name="permissions[]" value="Edit">
                                    <label for="checkUbah" class="form-check-label custom-control-label">Ubah</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input {{ $errors->has('permissions') ? 'is-invalid' : '' }} check" id="checkHapus" name="permissions[]" value="Delete">
                                    <label for="checkHapus" class="form-check-label custom-control-label">Hapus</label>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('permissions'))
                        <div class="row">
                            <div class="text-danger">{{ $errors->first('permissions') }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered text-center" id="dynamicAddRemove">
                        <tr>
                            <th class="w-75">Custom Permission</th>
                            <th class="w-25"><button type="button" id="dynamic-ar" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button></th>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-sm-12 mt-5">
                <button type="submit" class="btn btn-primary float-lg-end btn-sm">Submit</button>
              </div>
          </form>
        </div>
      </div>
  </div>
</div>

@push('js')
<script>
    $(document).ready(function () {
        $(".checkAll").on('change', function () {
            if ($(this).is(':checked')) {
                $(".check").prop('checked', true);
            } else {
                $(".check").prop('checked', false);
            }
        });

        var i = 5;
        $("#dynamic-ar").click(function () {
            ++i;
            $("#dynamicAddRemove").append(
                `<tr>
                    <td>
                        <input type="text" class="form-control" name="permissions[` + i + `]" placeholder="Masukkan Custom Permission">
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
