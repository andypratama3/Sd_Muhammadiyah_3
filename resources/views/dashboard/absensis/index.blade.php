@extends('layouts.dashboard')

@section('title', 'Absensi Online')

@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="{{ asset('kml/style.css') }}">
<link rel="stylesheet" href="{{ asset('kml/smooth-zoom.css') }}">
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── Page Heading ── --}}
    <div class="mb-4 d-sm-flex align-items-center justify-content-between">
        <h1 class="mb-0 text-gray-800 h3">
            <i class="fas fa-calendar-check text-primary"></i> Absensi Online
        </h1>
        <span id="lokasi-label" class="badge bg-secondary">
            <i class="fas fa-map-marker-alt"></i> Mendeteksi lokasi...
        </span>
    </div>

    {{-- ── Time Display ── --}}
    <div class="mb-4 row">
        <div class="col-12">
            <div class="time-display">
                <div class="row align-items-center">
                    <div class="mb-3 text-center col-md-6 text-md-start mb-md-0">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                            <i class="fas fa-clock fa-3x me-3"></i>
                            <div>
                                <h2 class="mb-0 text-black fw-bold" id="waktu-sekarang">00:00:00</h2>
                                <p class="mb-0 opacity-75">WITA (Waktu Indonesia Tengah)</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center col-md-6 text-md-end">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-end">
                            <i class="fas fa-calendar-alt fa-2x me-3"></i>
                            <div>
                                <p class="mb-0 opacity-75">Tanggal Hari Ini</p>
                                <h5 class="mb-0 text-black fw-bold" id="tanggal-sekarang">Loading...</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Row ── --}}
    <div class="row">

        {{-- ── Form Absensi ── --}}
        <div class="mb-4 col-lg-8">
            <div class="card info-card">
                <div class="py-3 bg-white card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-edit"></i> Form Absensi
                    </h6>
                </div>
                <div class="card-body">
                    <form id="form-absensi">

                        {{-- Info Pegawai --}}
                        <div class="mb-4">
                            <span class="text-black">{{ auth()->user()->name }}</span>
                            @isset($jenisPegawai)
                                <small class="ms-2">
                                    <span class="badge bg-primary">{{ $jenisPegawai }}</span>
                                </small>
                            @endisset
                        </div>

                        {{-- ── Absensi Kerja ── --}}
                        <p class="mb-2 text-muted" style="font-size:11px; letter-spacing:1.5px; text-transform:uppercase; font-weight:600;">
                            <i class="fas fa-school me-1"></i> Absensi Kerja
                        </p>
                        <div class="mb-3 row g-3">
                            <div class="col-md-6">
                                <button type="button" id="btn-absen-masuk"
                                    class="btn btn-success btn-absensi w-100">
                                    <i class="fas fa-sign-in-alt"></i> Absen Masuk
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" id="btn-absen-pulang"
                                    class="btn btn-danger btn-absensi w-100">
                                    <i class="fas fa-sign-out-alt"></i> Absen Pulang
                                </button>
                            </div>
                        </div>

                        {{-- ── Divider ── --}}
                        <hr class="my-3">

                        {{-- ── Absensi Sholat ── --}}
                        <p class="mb-2 text-muted" style="font-size:11px; letter-spacing:1.5px; text-transform:uppercase; font-weight:600;">
                            <i class="fas fa-mosque me-1"></i> Absensi Sholat
                        </p>
                        <div class="mb-1">
                            <button type="button" id="btn-absen-sholat"
                                class="btn btn-absensi w-100"
                                style="background-color:#059669; border-color:#059669; color:#fff;">
                                <i class="fas fa-mosque"></i> Absen Sholat
                            </button>
                        </div>
                        <div class="mb-1">
                            <button type="button" id="btn-izin-absen-sholat"
                                class="btn btn-absensi w-100"
                                style="background-color:#059669; border-color:#059669; color:#fff;">
                                <i class="fas fa-mosque"></i> Izin/Berhalangan
                            </button>
                        </div>
                        <p class="mb-3 text-muted" style="font-size:11px;">
                            <i class="fas fa-info-circle"></i>
                            Harus berada di <strong>Area Sholat</strong> (area hijau pada peta). Dapat dilakukan beberapa kali per hari.
                        </p>

                        <hr class="my-3">

                        {{-- ── Riwayat ── --}}
                        <div class="row g-2">
                            <div class="col-md-6">
                                <button type="button" id="btn-riwayat"
                                    class="btn btn-secondary btn-absensi w-100">
                                    <i class="fas fa-history"></i> Riwayat Kerja
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" id="btn-riwayat-sholat"
                                    class="btn btn-outline-secondary btn-absensi w-100">
                                    <i class="fas fa-mosque"></i> Riwayat Sholat
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- ── Info Card ── --}}
        <div class="mb-4 col-lg-4">
            <div class="shadow card info-card border-left-info h-100">
                <div class="py-3 text-white card-header bg-info">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Informasi Penting
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mt-3 mb-3">
                        <i class="fas fa-clock text-success"></i>
                        <strong>Jam Kerja:</strong><br>
                        @isset($jamKerja)
                            <small>
                                {{ ucfirst($jamKerja->hari) }} :
                                {{ \Carbon\Carbon::parse($jamKerja->jam_masuk)->format('H:i') }}
                                –
                                {{ \Carbon\Carbon::parse($jamKerja->jam_pulang)->format('H:i') }}
                                WITA
                            </small>
                        @else
                            <small class="text-muted">Jam kerja belum dikonfigurasi</small>
                        @endisset
                    </div>

                    <h6 class="mb-3 fw-bold">
                        <i class="fas fa-check-circle text-success"></i> Persyaratan:
                    </h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            GPS/Lokasi harus aktif
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-unlock-alt text-warning"></i>
                            Izinkan akses lokasi browser
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-draw-polygon text-primary"></i>
                            Absen kerja: berada di <strong>area biru</strong>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-mosque" style="color:#059669"></i>
                            Absen sholat: berada di <strong>area hijau</strong>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-info"></i>
                            Waktu sistem: WITA
                        </li>
                    </ul>

                    <div class="p-2 mt-3 mb-0 alert alert-warning" style="font-size:12px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian!</strong><br>
                        Absensi hanya dapat dilakukan di lokasi yang telah ditentukan pada peta.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Peta ── --}}
    <div class="mt-2 row">
        <div class="col-12">
            <div class="card info-card">
                <div class="py-3 bg-white card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-map-marked-alt"></i> Area Absensi - Peta Lokasi
                    </h6>
                </div>
                <div class="card-body position-relative">
                    <div id="map-status" class="map-status-badge">
                        <i class="fas fa-spinner fa-spin"></i> Memuat lokasi...
                    </div>

                    <div id="map-container"></div>

                    {{-- Legenda --}}
                    <div class="mt-3 map-legend">
                        <h6 class="mb-3 fw-bold">
                            <i class="fas fa-info-circle"></i> Legenda Peta
                        </h6>
                        <div class="legend-item">
                            <div class="legend-color"
                                style="background-color:rgba(66,153,225,0.3); border:2px solid #4299e1;"></div>
                            <span>
                                <strong>Area Kerja</strong>
                                — Wilayah absen masuk &amp; pulang
                            </span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color"
                                style="background-color:rgba(72,187,120,0.3); border:2px solid #48bb78;"></div>
                            <span>
                                <strong>Area Sholat</strong>
                                — Wilayah absen sholat
                            </span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color"
                                style="background-color:#48bb78; border-radius:50%;"></div>
                            <span><strong>Lokasi Anda</strong> — Posisi GPS real-time</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color"
                                style="background-color:rgba(66,153,225,0.1); border:2px dashed #4299e1;"></div>
                            <span><strong>Akurasi GPS</strong> — Radius akurasi GPS Anda</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Riwayat Kerja ── --}}
    <div id="riwayat-container" class="mt-4 row d-none">
        <div class="col-12">
            <div class="card info-card">
                <div class="py-3 bg-white card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-alt"></i> Riwayat Absensi Kerja
                    </h6>
                </div>
                <div class="card-body">
                    <div id="info-pegawai" class="mb-4"></div>
                    <div id="riwayat-list"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Riwayat Sholat ── --}}
    <div id="riwayat-sholat-container" class="mt-4 row d-none">
        <div class="col-12">
            <div class="card info-card">
                <div class="py-3 bg-white card-header d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold" style="color:#059669;">
                        <i class="fas fa-mosque"></i> Riwayat Absensi Sholat
                    </h6>
                    <span id="riwayat-sholat-total" class="badge"
                        style="background:#059669; font-size:12px;"></span>
                </div>
                <div class="card-body">
                    <div id="riwayat-sholat-list"></div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /container-fluid --}}

{{-- ── Notification ── --}}
<div class="notification-container" id="notification-container"></div>

{{-- ── Loading Overlay ── --}}
<div id="loading-overlay" class="loading-overlay d-none">
    <div class="loading-content">
        <div class="spinner-border spinner-border-lg text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="loading-text">Memproses absensi...</p>
    </div>
</div>

@endsection

@push('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('kml/absensi-notification.js') }}"></script>
<script src="{{ asset('kml/realtime-location.js') }}"></script>
<script src="{{ asset('kml/absensi-map.js') }}"></script>
<script src="{{ asset('kml/realtime-integration.js') }}"></script>
<script src="{{ asset('kml/absensi-main.js') }}"></script>
<script src="{{ asset('kml/focus-location-button.js') }}"></script>

{{-- ── Riwayat Sholat (inline, ringan) ── --}}
<script>
document.getElementById('btn-riwayat-sholat')?.addEventListener('click', async function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    showLoading();

    try {
        const now   = new Date();
        const bulan = now.getMonth() + 1;
        const tahun = now.getFullYear();

        const res = await fetch(`/dashboard/absensis/sholat/riwayat?bulan=${bulan}&tahun=${tahun}`, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const result = await res.json();

        if (!result.success) {
            notify.error(result.message || 'Gagal memuat riwayat sholat', 'Error');
            return;
        }

        const container = document.getElementById('riwayat-sholat-container');
        const list      = document.getElementById('riwayat-sholat-list');
        const totalBadge = document.getElementById('riwayat-sholat-total');

        container.classList.remove('d-none');

        const riwayat    = result.data?.riwayat    ?? [];
        const totalBulan = result.data?.total_bulan ?? 0;

        totalBadge.textContent = `${totalBulan}× bulan ini`;

        if (riwayat.length === 0) {
            list.innerHTML = `
                <div class="py-5 text-center">
                    <i class="mb-3 fas fa-mosque fa-3x text-muted"></i>
                    <p class="text-muted">Belum ada riwayat absensi sholat bulan ini</p>
                </div>`;
        } else {
            list.innerHTML = riwayat.map(item => `
                <div class="mb-3 border-0 shadow-sm card">
                    <div class="py-3 card-body">
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-calendar-day" style="color:#059669"></i>
                                ${escapeHtml(item.hari)}, ${escapeHtml(item.tanggal)}
                            </h6>
                            <span class="badge" style="background:#059669;">
                                ${item.izin > 0 ? 'Izin Telah Dicatat' : item.total + '× sholat'}
                            </span>
                        </div>
                        <div class="flex-wrap gap-2 d-flex">
                            ${item.detail.map(d => {
                                const isIzin = d.jenis_sholat === 'izin';
                                return `
                                    <span class="border badge ${isIzin ? 'bg-info text-white' : 'bg-light text-dark'}" style="font-size:12px;">
                                        <i class="fas ${isIzin ? 'fa-info-circle' : 'fa-clock'}" style="${isIzin ? 'color:#fff' : 'color:#059669'}"></i>
                                        ${isIzin ? 'Izin Berhalangan' : escapeHtml(d.nama_sholat) + ' ' + escapeHtml(d.jam_sholat) + ' WITA'}
                                        ${!isIzin ? `<small class="text-muted ms-1">${escapeHtml(d.area)}</small>` : ''}
                                    </span>
                                `;
                            }).join('')}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        setTimeout(() => {
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);

        notify.success('Riwayat sholat berhasil dimuat', 'Berhasil');

    } catch (err) {
        notify.error(err.message || 'Gagal memuat riwayat sholat', 'Error');
    } finally {
        hideLoading();
    }
});
</script>
@endpush
