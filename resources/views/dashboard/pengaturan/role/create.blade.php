@extends('layouts.dashboard')
@section('title','Tambah Role')

@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css">
@endpush

@section('content')

<div class="row">
    <div class="col-lg-12">
        <!-- Form Basic -->
        @include('layouts.flashmessage')
        <div class="mb-4 card">
            <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
                <h6 class="m-0 text-center font-weight-bold text-primary">Tambah Role</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('dashboard.pengaturan.role.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="name">Nama Role</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            id="name" name="name" placeholder="Masukan Nama Role" value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label>Pilih Hak Akses <code>*</code></label>
                            @error('permissions')
                                <div class="mt-2 alert alert-danger">{{ $message }}</div>
                            @enderror

                            <div class="table-responsive">
                                <table class="table mb-5 table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center">Permission</th>
                                            <th scope="col" class="text-center" style="width: 100px;">Pilih</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($permissions as $permission)
                                        <tr>
                                            <td>
                                                <strong>{{ $permission->name }}</strong>
                                                <br>
                                                <small class="text-muted">Guard: {{ $permission->guard_name }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                        class="custom-control-input permission-checkbox"
                                                        id="permission{{ $permission->id }}"
                                                        name="permissions[]"
                                                        value="{{ $permission->id }}"
                                                        @if(in_array($permission->id, old('permissions', []))) checked @endif>
                                                    <label for="permission{{ $permission->id }}" class="custom-control-label"></label>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center">Tidak ada permission</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary float-end btn-sm">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    $(document).ready(function () {
        // No complex select all logic needed with simplified table
    });
</script>
@endpush
