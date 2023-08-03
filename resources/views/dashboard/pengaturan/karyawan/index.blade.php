@extends('layouts.dashboard')
@section('title','Karyawan')
@section('content')
<div class="row">
    <div class="col-lg-12 mb-4">
      <!-- Simple Tables -->
      <div class="card">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
          <h4 class="m-0 font-weight-bold text-primary text-center">Karyawan</h4>
          <a href="{{ route('dashboard.pengaturan.karyawan.create') }}" class="btn btn-success float-end">Tambah</a>
        </div>
        <div class="table-responsive">
          <table class="table align-items-center table-flush text-center">
            <thead class="thead-light">
              <tr>
                <th>No</th>
                <th>Nama karyawan</th>
                <th>Email</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($karyawans as $karyawan)
              <tr>
                <td>{{ ++$no }}</td>
                <td>{{ $karyawan->name }}</td>
                <td>{{ $karyawan->sex }}</td>
                <td>{{ $karyawan->birth_date }}</td>
                <td>{{ $karyawan->phone }}</td>
                  <td>
                      <a href="{{ route('dashboard.pengaturan.karyawan.show', $karyawan->slug) }}" class="btn btn-dark"><i class="bi bi-eye">Detail</i></a>
                      <a href="{{ route('dashboard.pengaturan.karyawan.edit', $karyawan->slug) }}" class="btn btn-primary">Update</a>
                      <a href="#" data-id="{{ $karyawan->slug }}" class="btn btn-danger delete" title="Hapus">
                          <form action="{{ route('dashboard.pengaturan.karyawan.destroy', $karyawan->slug) }}" id="delete-{{ $karyawan->slug }}" method="POST" enctype="multipart/form-data">
                              @csrf
                              @method('delete')
                          </form>
                          <i class="fas fa-trash"></i>
                          Hapus
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

