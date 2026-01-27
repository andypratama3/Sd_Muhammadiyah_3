@extends('layouts.dashboard')

@section('title', 'Absensi Online')

@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="{{ asset('kml/style.css') }}">
<link rel="stylesheet" href="{{ asset('kml/smooth-zoom.css') }}">

@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="mb-4 d-sm-flex align-items-center justify-content-between">
        <h1 class="mb-0 text-gray-800 h3">
            <i class="fas fa-calendar-check text-primary"></i> Absensi Online
        </h1>
        <span id="lokasi-label" class="badge bg-secondary">
            <i class="fas fa-map-marker-alt"></i> Mendeteksi lokasi...
        </span>
    </div>

    <!-- Time Display Card -->
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
                                <h5 class="mb-0 text-black fw-bold" id="tanggal-sekarang">Loading...</h5>
                                <p class="mb-0 opacity-75">Tanggal Hari Ini</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Form Absensi Card -->
        <div class="mb-4 col-lg-8">
            <div class="card info-card">
                <div class="py-3 bg-white card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-edit"></i> Form Absensi
                    </h6>
                </div>
                <div class="card-body">
                    <form id="form-absensi">
                        <!-- NIP Input -->
                        <div class="mb-4">
                            <label for="nip" class="form-label fw-bold">
                                <i class="fas fa-id-card text-primary"></i> Nomor Induk Pegawai (NIP)
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-hashtag"></i>
                                </span>
                                <input
                                    type="text"
                                    id="nip"
                                    name="nip"
                                    maxlength="18"
                                    value="{{ old('nip', $karyawan->nip ?? '') }}"
                                    class="form-control"
                                    placeholder="Masukkan 18 digit NIP Anda"
                                    required
                                    autocomplete="off"
                                    />
                            </div>
                            <small class="mt-2 form-text text-muted">
                                <i class="fas fa-info-circle"></i> Contoh: 198501012010011001
                            </small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mb-3 row g-3">
                            <div class="col-md-6">
                                <button
                                    type="button"
                                    id="btn-absen-masuk"
                                    class="btn btn-success btn-absensi w-100">
                                    <i class="fas fa-sign-in-alt"></i> Absen Masuk
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button
                                    type="button"
                                    id="btn-absen-pulang"
                                    class="btn btn-danger btn-absensi w-100">
                                    <i class="fas fa-sign-out-alt"></i> Absen Pulang
                                </button>
                            </div>
                        </div>

                        <button
                            type="button"
                            id="btn-riwayat"
                            class="btn btn-secondary btn-absensi w-100">
                            <i class="fas fa-history"></i> Lihat Riwayat Absensi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Card -->
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
                                -
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
                            Berada dalam area yang ditampilkan di peta
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-info"></i>
                            Waktu sistem: WITA
                        </li>
                    </ul>

                    <div class="mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian!</strong><br>
                        <small>Absensi hanya dapat dilakukan di lokasi yang telah ditentukan</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAP SECTION -->
    <div class="mt-4 row">
        <div class="col-12">
            <div class="card info-card">
                <div class="py-3 bg-white card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-map-marked-alt"></i> Area Absensi - Peta Lokasi
                    </h6>
                </div>
                <div class="card-body position-relative">
                    <!-- Status Badge -->
                    <div id="map-status" class="map-status-badge">
                        <i class="fas fa-spinner fa-spin"></i> Memuat lokasi...
                    </div>

                    <!-- Map Container -->
                    <div id="map-container"></div>

                    <!-- Legend -->
                    <div class="mt-3 map-legend">
                        <h6 class="mb-3 fw-bold">
                            <i class="fas fa-info-circle"></i> Legenda Peta
                        </h6>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: rgba(66, 153, 225, 0.3); border: 2px solid #4299e1;"></div>
                            <span><strong>Area Absensi</strong> - Wilayah yang diizinkan untuk absensi</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #48bb78; border-radius: 50%;"></div>
                            <span><strong>Lokasi Anda</strong> - Posisi GPS real-time</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: rgba(66, 153, 225, 0.1); border: 2px dashed #4299e1;"></div>
                            <span><strong>Area Akurasi GPS</strong> - Radius akurasi GPS Anda</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Absensi -->
    <div id="riwayat-container" class="mt-4 row d-none">
        <div class="col-12">
            <div class="card info-card">
                <div class="py-3 bg-white card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-alt"></i> Riwayat Absensi
                    </h6>
                </div>
                <div class="card-body">
                    <div id="info-pegawai" class="mb-4"></div>
                    <div id="riwayat-list"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Container -->
<div class="notification-container" id="notification-container"></div>

<!-- Loading Overlay -->
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
<script>
setInterval(() => {
    const loc = window.realtimeTracker?.getCurrentLocation();
    const stats = window.realtimeTracker?.getStatistics();

    // if (loc) {
    //     console.clear();
    //     console.log('═'.repeat(60));
    //     console.log('🎯 REAL-TIME TRACKING DASHBOARD');
    //     console.log('═'.repeat(60));
    //     console.log(`📍 Position: ${loc.latitude.toFixed(6)}, ${loc.longitude.toFixed(6)}`);
    //     console.log(`📡 Accuracy: ±${loc.accuracy.toFixed(0)}m`);
    //     console.log(`🎯 Geofence: ${window.realtimeTracker.geofenceStatus?.toUpperCase()}`);
    //     console.log(`🚶 Distance from last: ${loc.distanceFromLast ? loc.distanceFromLast.toFixed(1) + 'm' : 'First position'}`);
    //     console.log(`📊 Total distance: ${stats.totalDistance.toFixed(0)}m`);
    //     console.log(`📈 Avg accuracy: ±${stats.avgAccuracy.toFixed(0)}m`);
    //     console.log(`📍 Updates: ${stats.totalLocations}`);
    //     console.log(`⏱️ Last update: ${loc.formattedTime}`);
    //     console.log('═'.repeat(60));
    // }
}, 1000);
// fuction test

// Simulasi jalan keluar area
// let step = 0;
// const baseX = -0.509317;
// const baseY = 117.130073;

// const walkOut = setInterval(() => {
//     step++;

//     // Bergerak 12m per step (past 10m threshold)
//     const newLat = baseX + (step * 0.00012);
//     const newLon = baseY + (step * 0.00012);

//     console.log(`🚶 Step ${step}: ${newLat.toFixed(6)}, ${newLon.toFixed(6)}`);

//     window.realtimeTracker?.processLocationUpdate({
//         coords: {
//             latitude: newLat,
//             longitude: newLon,
//             accuracy: 25,
//             altitude: null,
//             altitudeAccuracy: null,
//             heading: 45,
//             speed: 1.2
//         },
//         timestamp: Date.now()
//     });

//     if (step >= 15) {
//         clearInterval(walkOut);
//         console.log('✅ Simulation complete - check if you exited geofence!');
//     }
// }, 3000);
</script>
@endpush
