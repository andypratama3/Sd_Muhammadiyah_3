@extends('layouts.dashboard')

@section('title', 'Jam Kerja')

@section('content')
    <div class="mb-4 card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Data Jam Kerja</h6>
            <a href="{{ route('dashboard.jam.absen.create') }}" class="btn btn-sm btn-primary">
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
                            <th>Hari Kerja</th>
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
                                <td>{{ \Carbon\Carbon::parse($item->jam_masuk)->format('H:i:s') }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->batas_masuk)->format('H:i:s') }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->jam_pulang)->format('H:i:s') }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->batas_pulang)->format('H:i:s') }}</td>
                                <td class="text-center">
                                    @if ($item->is_default)
                                        <span class="badge bg-success">YA</span>
                                    @else
                                        <span class="badge bg-secondary">TIDAK</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm toggle-hari-kerja"
                                            data-id="{{ $item->id }}"
                                            data-status="{{ $item->is_hari_kerja ? 'true' : 'false' }}">
                                        @if ($item->is_hari_kerja)
                                            <span class="badge bg-success cursor-pointer">YA</span>
                                        @else
                                            <span class="badge bg-secondary cursor-pointer">TIDAK</span>
                                        @endif
                                    </button>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('dashboard.jam.absen.edit', $item->id) }}"
                                        class="btn btn-sm btn-warning">
                                        Edit
                                    </a>
                                    <a href="#" data-id="{{ $item->id }}" class="btn btn-danger btn-sm delete"
                                        title="Hapus">
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
                                <td colspan="11" class="text-center text-muted">
                                    Data jam kerja belum tersedia
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $jamKerja->links() }}
            </div>
        </div>
    </div>

@push('scripts')
<script>
    document.querySelectorAll('.toggle-hari-kerja').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const badge = this.querySelector('.badge');

            try {
                const response = await fetch(`/dashboard/pengaturan-absen/jam-absen/${id}/toggle-hari-kerja`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Update badge
                    if (data.is_hari_kerja) {
                        badge.classList.remove('bg-secondary');
                        badge.classList.add('bg-success');
                        badge.textContent = 'YA';
                    } else {
                        badge.classList.remove('bg-success');
                        badge.classList.add('bg-secondary');
                        badge.textContent = 'TIDAK';
                    }

                    // Show success message
                    if (typeof showAlert !== 'undefined') {
                        showAlert(data.message, 'success');
                    } else {
                        alert(data.message);
                    }
                } else {
                    alert('Gagal mengubah status');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            }
        });
    });
</script>
@endpush
@endsection
