@extends('layouts.dashboard')
@section('title','Karyawan')
@section('content')
<div class="row">
    <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
            @include('layouts.flashmessage')
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary text-center">Karyawan</h4>
                <a href="{{ route('dashboard.pengaturan.karyawan.create') }}"
                class="btn btn-success float-right">Tambah <i class="fas fa-plus"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Nama karyawan</th>
                            <th>Jenis Kelamin</th>
                            <th>No Hp</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($karyawans as $karyawan)
                        <tr>
                            <td>{{ ++$no }}</td>
                            <td>{{ $karyawan->name }}</td>
                            <td>{{ $karyawan->sex }}</td>
                            <td>{{ $karyawan->phone }}</td>
                                @foreach ($karyawan->user->roles as $role)
                                        <td>{{ $role->name }}</td>
                                @endforeach
                            <td>
                                <a href="{{ route('dashboard.pengaturan.karyawan.show', $karyawan->slug) }}"
                                    class="btn btn-dark btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('dashboard.pengaturan.karyawan.edit', $karyawan->slug) }}"
                                    class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
                                <a href="#" data-id="{{ $karyawan->slug }}" class="btn btn-danger btn-sm delete" title="Hapus">
                                    <form action="{{ route('dashboard.pengaturan.karyawan.destroy', $karyawan->slug) }}"
                                        id="delete-{{ $karyawan->slug }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('delete')
                                    </form>
                                    <i class="fas fa-trash"></i>

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer"></div>
        </div>
    </div>
</div>

@endsection
