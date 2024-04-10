@extends('layouts.dashboard')
@section('title','Tambah Karyawan')
@push('css')
<link rel="stylesheet" href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.min.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-lg-12">
        <!-- Form Basic -->
        <div class="card mb-4">

            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary text-center">Tambah Karyawan</h6>
            </div>
            @include('layouts.flashmessage')
            <div class="card-body">
                <form action="{{ route('dashboard.pengaturan.karyawan.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Karyawan <code>*</code></label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Masukkan Nama Karyawan" value="{{ old('name') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jenis Kelamin<code>*</code></label>
                                <select name="sex" id="" class="form-control">
                                    <option disabled selected>Pilih Jenis Kelamin</option>
                                    <option value="Laki-Laki" {{ old('sex') == 'Laki-laki' ? 'selected' : '' }}>Laki Laki</option>
                                    <option value="Perempuan" {{ old('sex') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email Karyawan <code>*</code></label>
                                <input type="text" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="Email Karyawan">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor Hp <code>*</code></label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    placeholder="Masukkan Hp Karyawan" value="{{ old('phone') }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Role Karyawan <code>*</code></label>
                                <select class="form-control select2bs4" id="role_id" name="role_id" style="width: 100%;">
                                    <option selected="selected" disabled>Pilih Role</option>
                                    <hr>
                                    @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="card-footer">
                <button onclick="window.location.href='{{ route('dashboard.pengaturan.karyawan.index') }}'" type="button" class="btn btn-sm btn-danger" aria-hidden="true">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary  float-lg-right">Submit</button>
            </div>
            </form>
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col-md-6 -->
</div>
<!-- /.row -->
@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function () {
        $('.select2-single').select2();
        $('.select2-single-placeholder').select2({
        placeholder: "Pilih Email Karyawan",
        allowClear: true
      });
      $('#email').on('input', function () {
          let email = $('#email').val();
          $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
          $.ajax({
            type: "POST",
            url: "{{ route('dashboard.pengaturan.get.email') }}",
            data: {
                email: email
            },
            dataType: "dataType",
            cache: false,
            success: function (response) {
                console.log(response);

                // if(response.status == 'success'){
                //    $('#email').addClass('is-invalid');

                // }else{
                //     // email.addClass('is-invalid');
                // }
            }
          });
      });
    });
</script>
@endpush
@endsection
