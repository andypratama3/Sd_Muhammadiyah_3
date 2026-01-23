@extends('layouts.dashboard')
@section('title', 'Foto Sekolah')

@section('content')
    <div class="mb-4 shadow card">
        <div class="py-3 card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Foto Sekolah</h6>
            <a href="{{ route('dashboard.datasekolah.foto_sekolah.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Foto
            </a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if ($fotoSekolah->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th style="width: 30%">Nama</th>
                                <th style="width: 40%">Foto</th>
                                <th style="width: 25%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fotoSekolah as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>
                                        <img src="{{ asset('storage/' . $item->foto) }}" alt="Foto" style="width: 200px; height: auto; object-fit: cover; border-radius: 5px;">
                                    </td>
                                    <td>
                                        <a href="{{ route('dashboard.datasekolah.foto_sekolah.edit', $item->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="#" data-id="{{ $item->slug }}" class="btn btn-danger btn-sm delete" title="Hapus">
                                            <form action="{{ route('dashboard.datasekolah.kelas.destroy', $item->slug) }}"
                                                id="delete-{{ $item->slug }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('delete')
                                            </form>
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus foto <strong>{{ $item->name }}</strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                <form action="{{ route('dashboard.datasekolah.foto_sekolah.destroy', $item->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-5 text-center text-muted">
                                        Belum ada data foto sekolah
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-5 text-center">
                    <i class="mb-3 fas fa-image fa-3x text-muted"></i>
                    <p class="text-muted">Belum ada foto sekolah. <a href="{{ route('dashboard.datasekolah.foto_sekolah.create') }}">Tambah sekarang</a></p>
                </div>
            @endif
        </div>
    </div>
@endsection
