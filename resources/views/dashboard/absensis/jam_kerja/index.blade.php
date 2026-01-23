@extends('layouts.dashboard')

@section('title', 'Jam Kerja')

@section('content')
<div class="mb-4 card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Jam Kerja</h6>
        <a href="{{ route('dashboard.jam.absen.create') }}"
           class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Tambah Jam Kerja
        </a>
    </div>

    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Shift</th>
                        <th>Jenis Pegawai</th>
                        <th>Hari</th>
                        <th>Jam Masuk</th>
                        <th>Batas Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Batas Pulang</th>
                        <th>Default</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jamKerja as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->nama_shift }}</td>
                            <td>{{ ucfirst($item->jenis_pegawai) }}</td>
                            <td>{{ ucfirst($item->hari) }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->batas_masuk)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->jam_pulang)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->batas_pulang)->format('H:i') }}</td>
                            <td class="text-center">
                                @if ($item->is_default)
                                    <span class="badge bg-success">YA</span>
                                @else
                                    <span class="badge bg-secondary">TIDAK</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('dashboard.jam.absen.edit', $item->id) }}"
                                   class="btn btn-sm btn-warning">
                                    Edit
                                </a>
                                  <a href="#" data-id="{{ $item->id }}" class="btn btn-danger btn-sm delete" title="Hapus">
                                    <form action="{{ route('dashboard.jam.absen.destroy', $item->id) }}"
                                        id="delete-{{ $item->id }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('delete')
                                    </form>
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">
                                Data jam kerja belum tersedia
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
