@extends('layouts.dashboard')
@section('title','Dashboard')
@section('content')

    {{-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
      <h1 class="h3 mb-0 text-gray-800">Simple Tables</h1>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./">Home</a></li>
        <li class="breadcrumb-item">Tables</li>
        <li class="breadcrumb-item active" aria-current="page">Simple Tables</li>
      </ol>
    </div> --}}

    <div class="row">
      <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
          <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Berita</h6>
            <a href="{{ route('dashboard.berita.create') }}" class="btn btn-success float-end">Tambah</a>
          </div>
          <div class="table-responsive">
            <table class="table align-items-center table-flush text-center">
              <thead class="thead-light">
                <tr>
                  <th>No</th>
                  <th>Judul</th>
                  <th>Deskripsi</th>
                  <th>Foto</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($beritas as $berita)
                <tr>
                  <td>{{ ++$no }}</td>
                  <td>{{ $berita->judul }}</td>
                  <td>{{ $berita->desc }}</td>
                  <td><img src="{{ asset('storage/img/berita/'.$berita->foto) }}" alt="" srcset="" style="width: 100%; height:90px"></td>
                    <td>
                        <a href="{{ route('dashboard.berita.show', $berita->slug) }}" class="btn btn-dark"><i class="bi bi-eye">Detail</i></a>
                        <a href="{{ route('dashboard.berita.edit', $berita->slug) }}" class="btn btn-primary">Update</a>
                        <a href="#" data-id="{{ $berita->slug }}" class="btn btn-danger delete" title="Hapus">
                            <form action="{{ route('dashboard.berita.destroy', $berita->slug) }}" id="delete-{{ $berita->slug }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('delete')
                            </form>
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
    <!--Row-->

    <!-- Modal Logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabelLogout" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabelLogout">Ohh No!</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to logout?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-primary" data-dismiss="modal">Cancel</button>
            <a href="login.html" class="btn btn-primary">Logout</a>
          </div>
        </div>
      </div>
    </div>

@endsection
