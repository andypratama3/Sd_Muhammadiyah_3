@extends('layouts.dashboard')
@section('title','User')
@section('content')
<div class="row">
    <div class="col-lg-12 mb-4">
      <!-- Simple Tables -->
      <div class="card">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
          <h4 class="m-0 font-weight-bold text-primary text-center">User</h4>
          {{-- <a href="{{ route('dashboard.pengaturan.user.create') }}" class="btn btn-success float-end">Tambah</a> --}}
        </div>
        <div class="table-responsive">
          <table class="table align-items-center table-flush text-center">
            <thead class="thead-light">
              <tr>
                <th>No</th>
                <th>Nama user</th>
                <th>Email</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($users as $user)
              <tr>
                <td>{{ ++$no }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                  <td>
                      <a href="{{ route('dashboard.pengaturan.user.show', $user->slug) }}" class="btn btn-dark"><i class="bi bi-eye">Detail</i></a>
                      <a href="{{ route('dashboard.pengaturan.user.edit', $user->slug) }}" class="btn btn-primary">Update</a>
                      <a href="#" data-id="{{ $user->slug }}" class="btn btn-danger delete" title="Hapus">
                          <form action="{{ route('dashboard.pengaturan.user.destroy', $user->slug) }}" id="delete-{{ $user->slug }}" method="POST" enctype="multipart/form-data">
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

