@extends('layouts.dashboard')

@section('title', 'Manajemen Device Absensi')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-mobile-alt"></i> Manajemen Device Absensi
        </h6>
        <button class="btn btn-sm btn-danger" id="btn-cleanup">
            <i class="fas fa-trash"></i> Hapus Device Tidak Aktif (>90 hari)
        </button>
    </div>

    <div class="card-body">

        {{-- Search --}}
        <form method="GET" class="mb-3">
            <div class="input-group" style="max-width: 400px;">
                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Cari nama karyawan..."
                       value="{{ $search ?? '' }}">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="fas fa-search"></i>
                </button>
                @if($search)
                    <a href="{{ route('dashboard.device.absensi.index') }}"
                       class="btn btn-outline-danger">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Karyawan</th>
                        <th>Jabatan</th>
                        <th class="text-center">Total Device</th>
                        <th class="text-center">Aktif</th>
                        <th class="text-center">Nonaktif</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($karyawans as $karyawan)
                        @php
                            $totalDevice  = $karyawan->devices->count();
                            $aktifDevice  = $karyawan->devices->where('is_active', true)->count();
                            $nonaktif     = $totalDevice - $aktifDevice;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration + ($karyawans->firstItem() - 1) }}</td>
                            <td>
                                <div class="fw-semibold">{{ $karyawan->name }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $karyawan->user?->roles?->first()?->name ?? '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $totalDevice }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $aktifDevice }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $nonaktif }}</span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info btn-show-device"
                                        data-id="{{ $karyawan->id }}"
                                        data-name="{{ $karyawan->name }}">
                                    <i class="fas fa-eye"></i> Lihat Device
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-muted">
                                <i class="mb-2 fas fa-inbox fa-2x d-block"></i>
                                Tidak ada data device
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $karyawans->links() }}
    </div>
</div>

{{-- ===================== MODAL DEVICE ===================== --}}
<div class="modal fade" id="modalDevice" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-mobile-alt"></i>
                    Device — <span id="modal-karyawan-name"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-device-body">
                <div class="py-4 text-center" id="modal-loading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Memuat data...</p>
                </div>
                <div id="modal-device-list" class="d-none"></div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button class="btn btn-danger btn-sm" id="btn-reset-karyawan">
                    <i class="fas fa-sync-alt"></i> Reset Semua Device
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
let currentKaryawanId = null;

// ─── Buka modal & load devices ─────────────────────────────────────────────
document.querySelectorAll('.btn-show-device').forEach(btn => {
    btn.addEventListener('click', function () {
        currentKaryawanId = this.dataset.id;
        document.getElementById('modal-karyawan-name').textContent = this.dataset.name;
        document.getElementById('modal-loading').classList.remove('d-none');
        document.getElementById('modal-device-list').classList.add('d-none');

        new bootstrap.Modal(document.getElementById('modalDevice')).show();
        loadDevices(currentKaryawanId);
    });
});

// ─── Load devices via AJAX ──────────────────────────────────────────────────
async function loadDevices(karyawanId) {
    try {
        const res  = await fetch(`/dashboard/device-absensi/${karyawanId}`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();

        document.getElementById('modal-loading').classList.add('d-none');
        const list = document.getElementById('modal-device-list');
        list.classList.remove('d-none');

        if (!data.devices.length) {
            list.innerHTML = `<p class="text-center text-muted">Tidak ada device terdaftar.</p>`;
            return;
        }

        list.innerHTML = data.devices.map(d => `
            <div class="card mb-2 border ${d.is_active ? 'border-success' : 'border-secondary'}"
                 id="device-card-${d.id}">
                <div class="px-3 py-2 card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold">
                                <i class="fas fa-mobile-alt"></i> ${escapeHtml(d.device_name)}
                                ${d.is_stale
                                    ? '<span class="badge bg-warning ms-1">Tidak aktif lama</span>'
                                    : ''}
                            </div>
                            <small class="text-muted d-block">IP: ${escapeHtml(d.ip_address ?? '-')}</small>
                            <small class="text-muted d-block">Device ID: ${escapeHtml(d.device_id ?? '-')}</small>
                            <small class="text-muted d-block">
                                Terakhir digunakan: <strong>${d.last_used}</strong>
                            </small>
                            <small class="text-muted d-block">Terdaftar: ${d.registered}</small>
                        </div>
                        <div class="gap-1 d-flex flex-column ms-3">
                            <span class="badge ${d.is_active ? 'bg-success' : 'bg-secondary'} mb-1">
                                ${d.is_active ? 'Aktif' : 'Nonaktif'}
                            </span>
                            <button class="btn btn-xs btn-outline-warning btn-toggle"
                                    data-id="${d.id}">
                                <i class="fas fa-power-off"></i>
                                ${d.is_active ? 'Nonaktifkan' : 'Aktifkan'}
                            </button>
                            <button class="btn btn-xs btn-outline-danger btn-delete-device"
                                    data-id="${d.id}"
                                    data-name="${escapeHtml(d.device_name)}">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        // Bind event setelah render
        bindDeviceEvents();

    } catch (err) {
        document.getElementById('modal-loading').classList.add('d-none');
        document.getElementById('modal-device-list').innerHTML =
            `<p class="text-center text-danger">Gagal memuat data device.</p>`;
    }
}

// ─── Toggle aktif/nonaktif ──────────────────────────────────────────────────
function bindDeviceEvents() {
    document.querySelectorAll('.btn-toggle').forEach(btn => {
        btn.addEventListener('click', async function () {
            const id = this.dataset.id;
            const res  = await fetch(`/dashboard/device-absensi/${id}/toggle`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                loadDevices(currentKaryawanId);
            }
        });
    });

    document.querySelectorAll('.btn-delete-device').forEach(btn => {
        btn.addEventListener('click', async function () {
            if (!confirm(`Hapus device "${this.dataset.name}"?`)) return;
            const id = this.dataset.id;
            const res  = await fetch(`/dashboard/device-absensi/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                document.getElementById(`device-card-${id}`)?.remove();

                // Tutup modal & reload tabel jika tidak ada device tersisa
                if (!document.querySelectorAll('[id^="device-card-"]').length) {
                    bootstrap.Modal.getInstance(document.getElementById('modalDevice'))?.hide();
                    window.location.reload();
                }
            }
        });
    });
}

// ─── Reset semua device karyawan ────────────────────────────────────────────
document.getElementById('btn-reset-karyawan').addEventListener('click', async function () {
    if (!currentKaryawanId) return;
    const name = document.getElementById('modal-karyawan-name').textContent;
    if (!confirm(`Reset SEMUA device milik ${name}? Karyawan harus daftar ulang device saat absen berikutnya.`)) return;

    const res  = await fetch(`/dashboard/device-absensi/${currentKaryawanId}/reset`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalDevice'))?.hide();
        window.location.reload();
    }
});

// ─── Cleanup device stale ────────────────────────────────────────────────────
document.getElementById('btn-cleanup').addEventListener('click', async function () {
    if (!confirm('Hapus semua device yang tidak digunakan lebih dari 90 hari?')) return;

    const res  = await fetch('/dashboard/device-absensi/cleanup', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        setTimeout(() => window.location.reload(), 1500);
    }
});

// ─── Helper ─────────────────────────────────────────────────────────────────
function escapeHtml(text) {
    if (!text) return '-';
    return String(text).replace(/[&<>"']/g, m =>
        ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])
    );
}

function showToast(message, type = 'success') {
    // Gunakan notify jika tersedia (dari absensi-notification.js), fallback ke alert
    if (typeof notify !== 'undefined') {
        notify[type](message);
    } else {
        alert(message);
    }
}
</script>

<style>
.btn-xs {
    padding: 2px 8px;
    font-size: 0.75rem;
}
</style>
@endpush
