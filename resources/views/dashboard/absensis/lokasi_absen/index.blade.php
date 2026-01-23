@extends('layouts.dashboard')
@section('title', 'Pembayaran Siswa')

@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title"> Data Lokasi Absen</h4>
            <a href="{{ route('dashboard.lokasi.absen.create') }}" class="float-right btn btn-success btn-sm">
                Tambah
                <i class="fas fa-plus"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="mt-4 table-responsive">
                <table class="table mt-4 w-100" id="spmb_table" >
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lokasi</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Radius</th>
                            <th>Alamat</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lokasiAbsensi as $lokasi)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $lokasi->nama_lokasi }}</td>
                            <td>{{ $lokasi->latitude }}</td>
                            <td>{{ $lokasi->longitude }}</td>
                            <td>{{ $lokasi->radius }}</td>
                            <td>{{ $lokasi->alamat }}</td>
                            <td>{{ $lokasi->status }}</td>
                            <td>
                                <a href="{{ route('dashboard.lokasi.absen.edit', $lokasi->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                               <a href="#" data-id="{{ $lokasi->id }}" class="btn btn-danger btn-sm delete" title="Hapus">
                                    <form action="{{ route('dashboard.lokasi.absen.destroy', $lokasi->id) }}"
                                        id="delete-{{ $lokasi->id }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('delete')
                                    </form>
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak Ada Data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
