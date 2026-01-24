@extends('layouts.dashboard')

@section('title', 'Absensi Online')

@push('css')
<style>
    :root {
        --primary-color: #667eea;
        --success-color: #48bb78;
        --danger-color: #f56565;
        --warning-color: #ed8936;
        --info-color: #4299e1;
    }

    .time-display {
        background: var(--bs-primary);
        border-radius: 15px;
        padding: 20px;
        color: white;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }

    .btn-absensi {
        padding: 15px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-absensi:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-absensi i {
        font-size: 20px;
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .riwayat-card {
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }

    .riwayat-card:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .info-card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    /* Notification Toast Styles */
    .notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
        pointer-events: none;
    }

    .notification-toast {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        margin-bottom: 15px;
        overflow: hidden;
        pointer-events: all;
        animation: slideInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(400px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(400px);
        }
    }

    .notification-toast.removing {
        animation: slideOutRight 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .notification-toast.success {
        border-left: 5px solid var(--success-color);
    }

    .notification-toast.error {
        border-left: 5px solid var(--danger-color);
    }

    .notification-toast.warning {
        border-left: 5px solid var(--warning-color);
    }

    .notification-toast.info {
        border-left: 5px solid var(--info-color);
    }

    .notification-content {
        padding: 16px 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .notification-icon {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 14px;
        margin-top: 2px;
    }

    .notification-toast.success .notification-icon {
        background-color: rgba(72, 187, 120, 0.1);
        color: var(--success-color);
    }

    .notification-toast.error .notification-icon {
        background-color: rgba(245, 101, 101, 0.1);
        color: var(--danger-color);
    }

    .notification-toast.warning .notification-icon {
        background-color: rgba(237, 137, 54, 0.1);
        color: var(--warning-color);
    }

    .notification-toast.info .notification-icon {
        background-color: rgba(66, 153, 225, 0.1);
        color: var(--info-color);
    }

    .notification-text {
        flex: 1;
    }

    .notification-title {
        font-weight: 600;
        font-size: 14px;
        margin: 0;
        color: #2d3748;
    }

    .notification-message {
        font-size: 13px;
        color: #718096;
        margin: 4px 0 0 0;
        line-height: 1.4;
    }

    .notification-close {
        flex-shrink: 0;
        background: none;
        border: none;
        color: #cbd5e0;
        cursor: pointer;
        font-size: 18px;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
        margin-top: 2px;
    }

    .notification-close:hover {
        color: #2d3748;
    }

    /* Progress bar untuk auto-close */
    .notification-progress {
        height: 3px;
        background: currentColor;
        animation: progress linear var(--duration, 5s) forwards;
        opacity: 0.3;
    }

    @keyframes progress {
        from {
            width: 100%;
        }
        to {
            width: 0%;
        }
    }

    .notification-toast.error .notification-progress {
        background-color: var(--danger-color);
    }

    .notification-toast.success .notification-progress {
        background-color: var(--success-color);
    }

    /* Responsive */
    @media (max-width: 576px) {
        .notification-container {
            left: 10px;
            right: 10px;
            max-width: none;
        }

        .notification-toast {
            margin-bottom: 10px;
        }
    }

    /* Loading Spinner */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9998;
        backdrop-filter: blur(2px);
        animation: fadeIn 0.2s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .loading-content {
        text-align: center;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }

    .spinner-border-lg {
        width: 3rem;
        height: 3rem;
    }

    .loading-text {
        margin-top: 15px;
        font-weight: 600;
        color: var(--primary-color);
    }

    .d-none {
        display: none !important;
    }
</style>
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
                            <i class="fas fa-clock fa-3x me-3 location-icon"></i>
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
                                <p class="mb-0 opacity-75 text">Tanggal Hari Ini</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                    value="{{ old('nip') }}"
                                    class="form-control"
                                    placeholder="Masukkan 18 digit NIP Anda"
                                    required
                                    autocomplete="off">
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
                            <small class="text-muted">
                                Jam kerja belum dikonfigurasi
                            </small>
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
                            Berada dalam radius area Sekolah
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-info"></i>
                            Waktu sistem: WITA
                        </li>
                    </ul>

                    <div class="mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian!</strong><br>
                        <small>Absensi hanya dapat dilakukan di lokasi kantor yang telah ditentukan</small><br>
                        <small>Absensi hanya dapat dilakukan untuk 1 Smartphone untuk 1 akun</small>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="riwayat-container" class="row d-none">
        <div class="col-12">
            <div class="card info-card">
                <!-- Card Header -->
                <div class="py-3 bg-white card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-alt"></i> Riwayat Absensi
                    </h6>
                </div>

                <div class="card-body">
                    <div id="info-pegawai" class="mb-4 border-left-primary">
                    </div>
                    <div id="riwayat-list">
                    </div>
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
<script>
// Toast Notification System
// Toast Notification System
class ToastNotification {
    constructor() {
        this.container = document.getElementById('notification-container');
    }

    show(message, type = 'info', duration = 5000, title = '') {
        const id = Date.now();
        const iconMap = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        const titleMap = {
            success: 'Berhasil',
            error: 'Gagal',
            warning: 'Peringatan',
            info: 'Informasi'
        };

        const toast = document.createElement('div');
        toast.className = `notification-toast ${type}`;
        toast.id = `toast-${id}`;
        toast.innerHTML = `
            <div class="notification-content">
                <div class="notification-icon">
                    <i class="${iconMap[type]}"></i>
                </div>
                <div class="notification-text">
                    <p class="notification-title">${title || titleMap[type]}</p>
                    <p class="notification-message">${message}</p>
                </div>
                <button class="notification-close" onclick="this.closest('.notification-toast').dispatchEvent(new Event('close-toast'))">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            ${duration > 0 ? `<div class="notification-progress" style="--duration: ${duration}ms"></div>` : ''}
        `;

        this.container.appendChild(toast);

        toast.addEventListener('close-toast', () => {
            this.remove(id);
        });

        if (duration > 0) {
            setTimeout(() => this.remove(id), duration);
        }

        return id;
    }

    remove(id) {
        const toast = document.getElementById(`toast-${id}`);
        if (toast) {
            toast.classList.add('removing');
            setTimeout(() => {
                if (toast && toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }
    }

    success(message, title = '') {
        return this.show(message, 'success', 5000, title);
    }

    error(message, title = '') {
        return this.show(message, 'error', 6000, title);
    }

    warning(message, title = '') {
        return this.show(message, 'warning', 5000, title);
    }

    info(message, title = '') {
        return this.show(message, 'info', 4000, title);
    }
}

// Loading Overlay Control
function showLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        overlay.classList.remove('d-none');
    }
}

function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        overlay.classList.add('d-none');
    }
}

// Helper function to escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// ============================================
// DEVICE FINGERPRINTING FUNCTIONS
// ============================================

function getDeviceId() {
    let deviceId = localStorage.getItem('device_id');

    if (!deviceId) {
        deviceId = generateUniqueId();
        localStorage.setItem('device_id', deviceId);
        console.log('🆕 Device ID baru dibuat:', deviceId);
    } else {
        console.log('✅ Device ID ditemukan:', deviceId);
    }

    return deviceId;
}

function generateUniqueId() {
    const timestamp = Date.now();
    const random = Math.random().toString(36).substring(2, 15);
    const screen = `${window.screen.width}x${window.screen.height}`;
    const tz = new Date().getTimezoneOffset();

    const data = `${timestamp}-${random}-${screen}-${tz}`;
    return btoa(data).substring(0, 32);
}

function getDeviceInfo() {
    return {
        device_id: getDeviceId(),
        screen_resolution: `${window.screen.width}x${window.screen.height}`,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        platform: navigator.platform,
        language: navigator.language
    };
}

function getBrowserName() {
    const ua = navigator.userAgent;
    if (ua.indexOf('Firefox') > -1) return 'Firefox';
    if (ua.indexOf('Chrome') > -1) return 'Chrome';
    if (ua.indexOf('Safari') > -1) return 'Safari';
    if (ua.indexOf('Edge') > -1) return 'Edge';
    return 'Unknown Browser';
}

// ============================================
// MAIN SCRIPT
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const notify = new ToastNotification();

    // Log Device Info untuk debugging
    console.log('🔒 Device Fingerprint:', getDeviceId());
    console.log('📱 Browser:', getBrowserName());
    console.log('💻 Platform:', navigator.platform);

    // Update waktu real-time
    function updateWaktu() {
        const now = new Date();
        const options = {
            timeZone: 'Asia/Makassar',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        const waktu = now.toLocaleTimeString('id-ID', options);

        const optionsTanggal = {
            timeZone: 'Asia/Makassar',
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        const tanggal = now.toLocaleDateString('id-ID', optionsTanggal);

        const waktuEl = document.getElementById('waktu-sekarang');
        const tanggalEl = document.getElementById('tanggal-sekarang');

        if (waktuEl) waktuEl.textContent = waktu;
        if (tanggalEl) tanggalEl.textContent = tanggal;
    }

    updateWaktu();
    setInterval(updateWaktu, 1000);

    // Get User Location
    function getLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                return reject(new Error('Geolocation tidak didukung oleh browser Anda'));
            }

            navigator.geolocation.getCurrentPosition(
                pos => {
                    console.log('📍 Lokasi ditemukan:', pos.coords.latitude, pos.coords.longitude);
                    resolve({
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude
                    });
                },
                err => {
                    let msg = 'Gagal mendapatkan lokasi. ';
                    switch(err.code) {
                        case err.PERMISSION_DENIED:
                            msg += 'Akses lokasi ditolak. Mohon izinkan akses lokasi di pengaturan browser.';
                            break;
                        case err.POSITION_UNAVAILABLE:
                            msg += 'Informasi lokasi tidak tersedia.';
                            break;
                        case err.TIMEOUT:
                            msg += 'Waktu permintaan lokasi habis. Coba lagi.';
                            break;
                        default:
                            msg += 'Terjadi kesalahan tidak dikenal.';
                    }
                    console.error('❌ Error lokasi:', err);
                    reject(new Error(msg));
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    }

    async function getNamaKota(lat, lon) {
        try {
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=10&addressdetails=1`;

            const response = await fetch(url, {
                headers: {
                    'User-Agent': 'AbsensiApp/1.0'
                }
            });

            const data = await response.json();

            return data.address.city
                || data.address.town
                || data.address.village
                || data.address.county
                || 'Tidak diketahui';
        } catch (error) {
            console.error('Error mendapatkan nama kota:', error);
            return 'Tidak diketahui';
        }
    }

    async function tampilkanKotaUser() {
        try {
            const lokasi = await getLocation();
            const kota = await getNamaKota(lokasi.latitude, lokasi.longitude);

            document.getElementById('lokasi-label').innerHTML =
                `<i class="fas fa-map-marker-alt"></i> ${kota}`;
            document.getElementById('lokasi-label').className = 'badge bg-success';

        } catch (error) {
            console.error('Error tampilkan kota:', error);
            document.getElementById('lokasi-label').innerHTML =
                '<i class="fas fa-map-marker-alt"></i> Lokasi tidak tersedia';
            document.getElementById('lokasi-label').className = 'badge bg-warning';
        }
    }

    tampilkanKotaUser();

    // ============================================
    // PROSES ABSENSI (WITH DEVICE TRACKING)
    // ============================================

    async function prosesAbsensi(tipe) {
        const nipInput = document.getElementById('nip');
        const nip = nipInput ? nipInput.value.trim() : '';

        if (!nip) {
            notify.warning('Mohon masukkan NIP terlebih dahulu');
            if (nipInput) nipInput.focus();
            return;
        }

        const btnMasuk = document.getElementById('btn-absen-masuk');
        const btnPulang = document.getElementById('btn-absen-pulang');
        const btnRiwayat = document.getElementById('btn-riwayat');

        showLoading();

        // Disable buttons
        if (btnMasuk) btnMasuk.disabled = true;
        if (btnPulang) btnPulang.disabled = true;
        if (btnRiwayat) btnRiwayat.disabled = true;

        try {
            const location = await getLocation();
            const deviceInfo = getDeviceInfo(); // ← DEVICE INFO

            const endpoint = tipe === 'masuk'
                ? "{{ route('absensi.masuk') }}"
                : "{{ route('absensi.pulang') }}";

            console.log('📤 Mengirim request absensi:', {
                nip,
                latitude: location.latitude,
                longitude: location.longitude,
                device_id: deviceInfo.device_id
            });

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    nip: nip,
                    latitude: location.latitude,
                    longitude: location.longitude,
                    lokasi_id: 1,
                    device_id: deviceInfo.device_id // ← DEVICE ID DIKIRIM
                })
            });


            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            const result = await response.json();
            console.log('📥 Response dari server:', result);

            if (result.success) {
                const tipeName = tipe === 'masuk' ? 'Masuk' : 'Pulang';
                let message = result.message;

                // Notifikasi khusus jika device baru
                if (result.data?.is_new_device) {
                    message += ' (Device baru berhasil didaftarkan)';
                    console.log('🆕 Device baru terdaftar');
                }

                notify.success(message, `Absen ${tipeName} Berhasil`);
            } else {
                const errorMsg = result.message || 'Terjadi kesalahan saat memproses absensi';
                notify.error(errorMsg, 'Gagal Absensi');
            }
        } catch (err) {
            notify.error(err.message || 'Terjadi kesalahan yang tidak diketahui', 'Terjadi Kesalahan');
            console.error('❌ Error absensi:', err);
        } finally {
            hideLoading();

            // Enable buttons
            if (btnMasuk) btnMasuk.disabled = false;
            if (btnPulang) btnPulang.disabled = false;
            if (btnRiwayat) btnRiwayat.disabled = false;
        }
    }

    // ============================================
    // TAMPILKAN RIWAYAT
    // ============================================

    async function tampilkanRiwayat() {
        const nipInput = document.getElementById('nip');
        const nip = nipInput ? nipInput.value.trim() : '';

        if (!nip) {
            notify.warning('Mohon masukkan NIP terlebih dahulu');
            if (nipInput) nipInput.focus();
            return;
        }

        showLoading();

        try {
            const now = new Date();
            const bulan = now.getMonth() + 1;
            const tahun = now.getFullYear();
            const url = "{{ route('absensi.riwayat') }}?nip=" + encodeURIComponent(nip) +
                        "&bulan=" + bulan + "&tahun=" + tahun;

            const response = await fetch(url, {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });

            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            const result = await response.json();

            if (!result.success) {
                notify.error(result.message || 'Gagal memuat riwayat', 'Gagal Memuat Riwayat');
                return;
            }

            const container = document.getElementById('riwayat-container');
            if (!container) {
                throw new Error('Element riwayat-container tidak ditemukan di halaman');
            }

            container.classList.remove('d-none');

            const pegawai = result.data?.pegawai;
            const riwayat = result.data?.riwayat || [];

            const infoPegawai = document.getElementById('info-pegawai');
            const riwayatList = document.getElementById('riwayat-list');

            if (!infoPegawai || !riwayatList) {
                throw new Error('Element tidak ditemukan di halaman');
            }

            // Set pegawai info
            if (pegawai) {
                infoPegawai.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-circle fa-3x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1 fw-bold">${escapeHtml(pegawai.nama || '-')}</h5>
                            <p class="mb-0"><i class="fas fa-id-badge"></i> NIP: ${escapeHtml(pegawai.nip || '-')}</p>
                            <p class="mb-0"><i class="fas fa-briefcase"></i> ${escapeHtml(pegawai.jabatan || '-')}</p>
                        </div>
                    </div>
                `;
            }

            // Set riwayat list
            if (riwayat.length > 0) {
                const riwayatHtml = riwayat.map(item => `
                    <div class="mb-3 card riwayat-card">
                        <div class="card-body">
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-calendar-day text-primary"></i>
                                    ${new Date(item.tanggal).toLocaleDateString('id-ID', {
                                        weekday: 'long',
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    })}
                                </h6>
                                ${item.status_masuk === 'terlambat'
                                    ? '<span class="text-white status-badge bg-danger"><i class="fas fa-clock"></i> Terlambat</span>'
                                    : item.status_masuk === 'tepat_waktu'
                                        ? '<span class="text-white status-badge bg-success"><i class="fas fa-check"></i> Tepat Waktu</span>'
                                        : ''
                                }
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2 d-flex align-items-start">
                                        <i class="mt-1 fas fa-sign-in-alt text-success me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">Jam Masuk</small>
                                            <strong class="text-success">${item.jam_masuk || '-'}</strong>
                                            <small class="d-block text-muted">
                                                <i class="fas fa-map-marker-alt"></i> Jarak: ${item.jarak_masuk || '-'} m
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <i class="mt-1 fas fa-sign-out-alt text-primary me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">Jam Pulang</small>
                                            <strong class="text-primary">${item.jam_pulang || '-'}</strong>
                                            <small class="d-block text-muted">
                                                <i class="fas fa-map-marker-alt"></i> Jarak: ${item.jarak_pulang || '-'} m
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');

                riwayatList.innerHTML = riwayatHtml;
            } else {
                riwayatList.innerHTML = `
                    <div class="py-5 text-center">
                        <i class="mb-3 fas fa-inbox fa-3x text-muted"></i>
                        <p class="text-muted">Belum ada riwayat absensi untuk bulan ini</p>
                    </div>
                `;
            }

            setTimeout(() => {
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);

            notify.success('Riwayat absensi berhasil dimuat', 'Berhasil');
        } catch (err) {
            console.error('Error detail:', err);
            notify.error(err.message || 'Gagal memuat riwayat absensi', 'Error');
        } finally {
            hideLoading();
        }
    }

    // ============================================
    // EVENT LISTENERS
    // ============================================

    const btnMasuk = document.getElementById('btn-absen-masuk');
    const btnPulang = document.getElementById('btn-absen-pulang');
    const btnRiwayat = document.getElementById('btn-riwayat');

    if (btnMasuk) btnMasuk.addEventListener('click', () => prosesAbsensi('masuk'));
    if (btnPulang) btnPulang.addEventListener('click', () => prosesAbsensi('pulang'));
    if (btnRiwayat) btnRiwayat.addEventListener('click', tampilkanRiwayat);

    // Auto format NIP - only numbers
    const nipInput = document.getElementById('nip');
    if (nipInput) {
        nipInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
        nipInput.focus();
    }
});
</script>
@endpush
