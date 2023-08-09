@extends('layouts.dashboard')
@section('title','Detail Karyawan')
{{-- @push('css')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush --}}

@section('content')
<div class="row">
    <div class="col-lg-12">
      <!-- Form Basic -->
      <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
          <h6 class="m-0 font-weight-bold text-primary text-center">Detail Karyawan</h6>
        </div>
            <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Karyawan </label>
                                <div>
                                    {{ $karyawan->name }}
                                </div>
                            </div>
                            <hr>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jenis Kelamin</label>
                                <div>
                                    {{ $karyawan->sex }}
                                </div>
                            </div>
                            <hr>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email Karyawan </label>
                                <div>
                                    {{ $karyawan->user->email }}
                                </div>
                            </div>
                            <hr>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor Hp </label>
                                <div>{{ $karyawan->phone }}</div>
                            </div>
                            <hr>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Role Karyawan </label>
                                    @foreach ($karyawan->user->roles as $role)
                                        <div>{{ $role->name }}</div>
                                    @endforeach
                            </div>
                            <hr>
                        </div>
                    </div>
            </div>
            <div class="card-footer">
                <button onclick="window.location.href='{{ route('dashboard.pengaturan.karyawan.index') }}'"
                    type="button" class="btn btn-sm btn-danger" aria-hidden="true">Cancel</button>
            </div>
            </form>
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col-md-6 -->
</div>
<!-- /.row -->
@push('javascript')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    $(function () {
        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    })
</script>
@endpush
@endsection
