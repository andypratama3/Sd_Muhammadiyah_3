/**
 * Main Absensi Form Logic
 * File: public/js/absensi-main.js
 *
 * Perubahan dari versi sebelumnya:
 * - Tambah prosesAbsensiSholat() untuk absen sholat
 * - Tombol sholat terhubung ke endpoint POST /dashboard/absensis/sholat
 */

// ============================================
// DEVICE FINGERPRINTING
// ============================================

const DEVICE_ID_KEY = 'absensi_device_id';

function getDeviceId() {
    let deviceId = localStorage.getItem(DEVICE_ID_KEY);

    if (!deviceId) {
        deviceId = generateStableUUID();
        localStorage.setItem(DEVICE_ID_KEY, deviceId);
        console.log('🆕 Device ID baru dibuat:', deviceId);
    } else {
        console.log('✅ Device ID ditemukan:', deviceId);
    }

    return deviceId;
}

function generateStableUUID() {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
        return crypto.randomUUID();
    }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}

function getDeviceInfo() {
    return {
        device_id:         getDeviceId(),
        screen_resolution: `${window.screen.width}x${window.screen.height}`,
        timezone:          Intl.DateTimeFormat().resolvedOptions().timeZone,
        platform:          navigator.platform,
        language:          navigator.language
    };
}

// ============================================
// LOCATION RETRIEVAL
// ============================================

function getLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            return reject(new Error('Geolocation tidak didukung oleh browser Anda'));
        }

        let attemptCount = 0;
        const maxAttempts = 2;

        function attemptGetLocation(isHighAccuracy, timeout, attemptNum) {
            console.log(`🔍 Attempt ${attemptNum}: ${isHighAccuracy ? 'High' : 'Normal'} accuracy (${timeout}ms)`);

            navigator.geolocation.getCurrentPosition(
                pos => {
                    console.log(`✅ GPS ditemukan (Attempt ${attemptNum})`);
                    resolve({
                        latitude:  pos.coords.latitude,
                        longitude: pos.coords.longitude,
                        accuracy:  pos.coords.accuracy
                    });
                },
                error => {
                    attemptCount++;
                    console.warn(`⚠️ Attempt ${attemptNum} gagal (${error.code}):`, error.message);

                    if (attemptCount < maxAttempts) {
                        setTimeout(() => attemptGetLocation(false, 15000, attemptNum + 1), 1000);
                    } else {
                        let msg = 'Gagal mendapatkan lokasi. ';
                        switch (error.code) {
                            case 1: msg += 'Akses lokasi ditolak. Mohon izinkan akses lokasi di pengaturan browser.'; break;
                            case 2: msg += 'Informasi lokasi tidak tersedia. Pastikan GPS aktif.'; break;
                            case 3: msg += 'Waktu permintaan lokasi habis. Pastikan GPS aktif dan sinyal kuat.'; break;
                            default: msg += 'Terjadi kesalahan tidak dikenal.';
                        }
                        reject(new Error(msg));
                    }
                },
                { enableHighAccuracy: isHighAccuracy, timeout, maximumAge: 0 }
            );
        }

        attemptGetLocation(true, 10000, 1);
    });
}

async function getNamaKota(lat, lon) {
    try {
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=10&addressdetails=1`;
        const response = await fetch(url, { headers: { 'User-Agent': 'AbsensiApp/1.0' } });
        const data     = await response.json();
        return data.address.city || data.address.town || data.address.village || data.address.county || 'Tidak diketahui';
    } catch {
        return 'Tidak diketahui';
    }
}

async function tampilkanKotaUser() {
    const badge = document.getElementById('lokasi-label');
    if (!badge) return;

    try {
        const lokasi = await getLocation();
        const kota   = await getNamaKota(lokasi.latitude, lokasi.longitude);
        badge.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${escapeHtml(kota)}`;
        badge.className = 'badge bg-success';
    } catch {
        badge.innerHTML = '<i class="fas fa-map-marker-alt"></i> Lokasi tidak tersedia';
        badge.className = 'badge bg-warning';
    }
}

// ============================================
// TIME DISPLAY
// ============================================

function updateWaktu() {
    const now = new Date();

    const waktu = now.toLocaleTimeString('id-ID', {
        timeZone: 'Asia/Makassar',
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
    });

    const tanggal = now.toLocaleDateString('id-ID', {
        timeZone: 'Asia/Makassar',
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    const waktuEl   = document.getElementById('waktu-sekarang');
    const tanggalEl = document.getElementById('tanggal-sekarang');

    if (waktuEl)   waktuEl.textContent   = waktu;
    if (tanggalEl) tanggalEl.textContent = tanggal;
}

// ============================================
// ABSENSI KERJA (MASUK / PULANG)
// ============================================

async function prosesAbsensi(tipe) {
    const btnMasuk   = document.getElementById('btn-absen-masuk');
    const btnPulang  = document.getElementById('btn-absen-pulang');
    const btnSholat  = document.getElementById('btn-absen-sholat');
    const btnRiwayat = document.getElementById('btn-riwayat');
    const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content || '';

    showLoading();
    [btnMasuk, btnPulang, btnSholat, btnRiwayat].forEach(btn => { if (btn) btn.disabled = true; });

    try {
        const location   = await getLocation();
        const deviceInfo = getDeviceInfo();

        const endpoint = tipe === 'masuk'
            ? '/dashboard/absensis/masuk'
            : '/dashboard/absensis/pulang';

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                latitude:  location.latitude,
                longitude: location.longitude,
                device_id: deviceInfo.device_id
            })
        });

        if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

        const result = await response.json();

        if (result.success) {
            const tipeName = tipe === 'masuk' ? 'Masuk' : 'Pulang';
            let message    = result.message;
            if (result.data?.is_new_device) message += ' (Device baru berhasil didaftarkan)';
            notify.success(message, `Absen ${tipeName} Berhasil`);
        } else {
            notify.error(result.message || 'Terjadi kesalahan saat memproses absensi', 'Gagal Absensi');
        }

    } catch (err) {
        console.error('❌ Error absensi:', err);
        notify.error(err.message || 'Terjadi kesalahan yang tidak diketahui', 'Terjadi Kesalahan');
    } finally {
        hideLoading();
        [btnMasuk, btnPulang, btnSholat, btnRiwayat].forEach(btn => { if (btn) btn.disabled = false; });
    }
}

// ============================================
// ABSENSI SHOLAT
// ============================================

async function prosesAbsensiSholat() {
    const btnMasuk   = document.getElementById('btn-absen-masuk');
    const btnPulang  = document.getElementById('btn-absen-pulang');
    const btnSholat  = document.getElementById('btn-absen-sholat');
    const btnRiwayat = document.getElementById('btn-riwayat');
    const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content || '';

    showLoading();
    [btnMasuk, btnPulang, btnSholat, btnRiwayat].forEach(btn => { if (btn) btn.disabled = true; });

    try {
        const location   = await getLocation();
        const deviceInfo = getDeviceInfo();

        console.log('🕌 Request absensi sholat:', {
            latitude:  location.latitude,
            longitude: location.longitude,
            device_id: deviceInfo.device_id
        });

        const response = await fetch('/dashboard/absensis/sholat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                latitude:  location.latitude,
                longitude: location.longitude,
                device_id: deviceInfo.device_id
            })
        });

        if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

        const result = await response.json();
        console.log('📥 Response sholat:', result);

        if (result.success) {
            const totalHariIni = result.data?.total_hari_ini ?? 1;
            const area         = result.data?.area ?? 'Area Sholat';
            notify.success(
                `${result.message} (${area} · ke-${totalHariIni} hari ini)`,
                'Absen Sholat Berhasil'
            );
        } else {
            notify.error(result.message || 'Gagal mencatat absensi sholat', 'Gagal Absen Sholat');
        }

    } catch (err) {
        console.error('❌ Error absensi sholat:', err);
        notify.error(err.message || 'Terjadi kesalahan', 'Terjadi Kesalahan');
    } finally {
        hideLoading();
        [btnMasuk, btnPulang, btnSholat, btnRiwayat].forEach(btn => { if (btn) btn.disabled = false; });
    }
}

// ============================================
// RIWAYAT ABSENSI KERJA
// ============================================

async function tampilkanRiwayat() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    showLoading();

    try {
        const now   = new Date();
        const bulan = now.getMonth() + 1;
        const tahun = now.getFullYear();

        const response = await fetch(`/dashboard/absensis/riwayat?bulan=${bulan}&tahun=${tahun}`, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });

        if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

        const result = await response.json();

        if (!result.success) {
            notify.error(result.message || 'Gagal memuat riwayat', 'Gagal Memuat Riwayat');
            return;
        }

        const container = document.getElementById('riwayat-container');
        if (!container) throw new Error('Element riwayat-container tidak ditemukan');

        container.classList.remove('d-none');

        const pegawai     = result.data?.pegawai;
        const riwayat     = result.data?.riwayat || [];
        const infoPegawai = document.getElementById('info-pegawai');
        const riwayatList = document.getElementById('riwayat-list');

        if (!infoPegawai || !riwayatList) throw new Error('Element tidak ditemukan');

        if (pegawai) {
            infoPegawai.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-circle fa-3x text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1 fw-bold">${escapeHtml(pegawai.nama || '-')}</h5>
                        <p class="mb-0"><i class="fas fa-briefcase"></i> ${escapeHtml(pegawai.jabatan || '-')}</p>
                        <p class="mb-0"><i class="fas fa-tag"></i> Jenis: ${escapeHtml(pegawai.jenis_pegawai || '-')}</p>
                    </div>
                </div>
            `;
        }

        if (riwayat.length > 0) {
            riwayatList.innerHTML = riwayat.map(item => {
                const tanggalFormatted = new Date(item.tanggal).toLocaleDateString('id-ID', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                });

                let statusBadge = '';
                if (item.status_masuk === 'tepat_waktu') {
                    statusBadge = '<span class="text-white status-badge bg-success"><i class="fas fa-check"></i> Tepat Waktu</span>';
                } else if (item.status_masuk === 'terlambat') {
                    statusBadge = '<span class="text-white status-badge bg-warning"><i class="fas fa-clock"></i> Terlambat</span>';
                }

                return `
                    <div class="mb-3 card riwayat-card">
                        <div class="card-body">
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-calendar-day text-primary"></i> ${tanggalFormatted}
                                </h6>
                                ${statusBadge}
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2 d-flex align-items-start">
                                        <i class="mt-1 fas fa-sign-in-alt text-success me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">Jam Masuk</small>
                                            <strong class="text-success">${item.jam_masuk || '-'}</strong>
                                            <small class="d-block text-muted">
                                                <i class="fas fa-map-marker-alt"></i> Jarak: ${item.jarak_masuk ?? '-'} m
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
                                                <i class="fas fa-map-marker-alt"></i> Jarak: ${item.jarak_pulang ?? '-'} m
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <small class="text-muted">
                                        <i class="fas fa-hourglass-half"></i> Durasi: ${item.durasi_kerja || '-'}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
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
        console.error('Error riwayat:', err);
        notify.error(err.message || 'Gagal memuat riwayat absensi', 'Error');
    } finally {
        hideLoading();
    }
}

// ============================================
// EVENT LISTENERS & INIT
// ============================================

document.addEventListener('DOMContentLoaded', function () {
    updateWaktu();
    setInterval(updateWaktu, 1000);

    tampilkanKotaUser();

    const btnMasuk   = document.getElementById('btn-absen-masuk');
    const btnPulang  = document.getElementById('btn-absen-pulang');
    const btnSholat  = document.getElementById('btn-absen-sholat');   // ← baru
    const btnRiwayat = document.getElementById('btn-riwayat');

    if (btnMasuk)   btnMasuk.addEventListener('click',   () => prosesAbsensi('masuk'));
    if (btnPulang)  btnPulang.addEventListener('click',  () => prosesAbsensi('pulang'));
    if (btnSholat)  btnSholat.addEventListener('click',  prosesAbsensiSholat);           // ← baru
    if (btnRiwayat) btnRiwayat.addEventListener('click', tampilkanRiwayat);

    console.log('✅ absensi-main.js loaded');
});
