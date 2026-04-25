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
                                    <div class="form-check form-switch" style="display: flex; justify-content: center;">
                                        <input class="form-check-input toggle-hari-kerja-switch"
                                               type="checkbox"
                                               id="switch-{{ $item->id }}"
                                               data-id="{{ $item->id }}"
                                               {{ $item->is_hari_kerja ? 'checked' : '' }}
                                               style="cursor: pointer;">
                                    </div>
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
    document.querySelectorAll('.toggle-hari-kerja-switch').forEach(switchEl => {
        switchEl.addEventListener('change', async function(e) {
            const id = this.dataset.id;
            const isChecked = this.checked;

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
                    // Update checkbox state based on response
                    this.checked = data.is_hari_kerja;

                    // Show success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.setAttribute('role', 'alert');
                    alertDiv.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;

                    // Insert alert at top of card-body
                    const cardBody = document.querySelector('.card-body');
                    cardBody.insertBefore(alertDiv, cardBody.firstChild);

                    // Auto-dismiss after 3 seconds
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 3000);
                } else {
                    // Revert checkbox
                    this.checked = !isChecked;
                    alert('Gagal mengubah status');
                }
            } catch (error) {
                console.error('Error:', error);
                // Revert checkbox
                this.checked = !isChecked;
                alert('Terjadi kesalahan');
            }
        });
    });
</script>
@endpush
@endsection
