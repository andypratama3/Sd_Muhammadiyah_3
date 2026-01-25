/**
 * Main Absensi Form Logic - FIXED VERSION (No Timeout)
 * File: public/js/absensi-main.js
 */

// ============================================
// DEVICE FINGERPRINTING
// ============================================

function getDeviceId() {
    let deviceId = localStorage.getItem('device_id');

    if (!deviceId) {
        deviceId = generateUniqueId();
        localStorage.setItem('device_id', deviceId);
        console.log('🆕 Device ID dibuat:', deviceId);
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

// ============================================
// LOCATION RETRIEVAL - FIXED VERSION
// ============================================

/**
 * Get user current location dengan automatic fallback & retry
 * FIXED: Handles timeout dengan graceful fallback
 */
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
                    console.log(`✅ GPS found (Attempt ${attemptNum})!`);
                    console.log(`   Accuracy: ±${pos.coords.accuracy.toFixed(0)}m`);
                    resolve({
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude,
                        accuracy: pos.coords.accuracy
                    });
                },
                error => {
                    attemptCount++;
                    console.warn(`⚠️ Attempt ${attemptNum} failed (${error.code}):`, error.message);

                    if (attemptCount < maxAttempts) {
                        // Retry dengan normal accuracy
                        console.log('🔄 Retrying dengan normal accuracy...');
                        setTimeout(() => {
                            attemptGetLocation(false, 15000, attemptNum + 1);
                        }, 1000);
                    } else {
                        // Semua attempt gagal
                        console.error('❌ Semua attempt gagal');
                        let msg = 'Gagal mendapatkan lokasi. ';

                        switch(error.code) {
                            case 1: // PERMISSION_DENIED
                                msg += 'Akses lokasi ditolak. Mohon izinkan akses lokasi di pengaturan browser.';
                                break;
                            case 2: // POSITION_UNAVAILABLE
                                msg += 'Informasi lokasi tidak tersedia. Pastikan GPS aktif.';
                                break;
                            case 3: // TIMEOUT
                                msg += 'Waktu permintaan lokasi habis. Pastikan GPS aktif, sinyal kuat, dan di area outdoor. Coba lagi.';
                                break;
                            default:
                                msg += 'Terjadi kesalahan tidak dikenal.';
                        }

                        reject(new Error(msg));
                    }
                },
                {
                    enableHighAccuracy: isHighAccuracy,
                    timeout: timeout,
                    maximumAge: 0
                }
            );
        }

        // Start dengan high accuracy attempt
        attemptGetLocation(true, 10000, 1);
    });
}

/**
 * Get city name from coordinates
 */
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

/**
 * Display user city on badge
 */
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

// ============================================
// TIME DISPLAY
// ============================================

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

// ============================================
// ABSENSI PROCESSING
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    showLoading();

    if (btnMasuk) btnMasuk.disabled = true;
    if (btnPulang) btnPulang.disabled = true;
    if (btnRiwayat) btnRiwayat.disabled = true;

    try {
        // Get location dengan fallback
        const location = await getLocation();
        const deviceInfo = getDeviceInfo();

        const endpoint = tipe === 'masuk'
            ? "/dashboard/absensis/masuk"
            : "/dashboard/absensis/pulang";

        console.log('📤 Mengirim request absensi:', {
            nip,
            latitude: location.latitude,
            longitude: location.longitude,
            accuracy: location.accuracy,
            device_id: deviceInfo.device_id
        });

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                nip: nip,
                latitude: location.latitude,
                longitude: location.longitude,
                device_id: deviceInfo.device_id
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

        if (btnMasuk) btnMasuk.disabled = false;
        if (btnPulang) btnPulang.disabled = false;
        if (btnRiwayat) btnRiwayat.disabled = false;
    }
}

// ============================================
// RIWAYAT ABSENSI
// ============================================

async function tampilkanRiwayat() {
    const nipInput = document.getElementById('nip');
    const nip = nipInput ? nipInput.value.trim() : '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

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
        const url = "/dashboard/absensis/riwayat?nip=" + encodeURIComponent(nip) +
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
            throw new Error('Element riwayat-container tidak ditemukan');
        }

        container.classList.remove('d-none');

        const pegawai = result.data?.pegawai;
        const riwayat = result.data?.riwayat || [];

        const infoPegawai = document.getElementById('info-pegawai');
        const riwayatList = document.getElementById('riwayat-list');

        if (!infoPegawai || !riwayatList) {
            throw new Error('Element tidak ditemukan');
        }

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
        console.error('Error:', err);
        notify.error(err.message || 'Gagal memuat riwayat absensi', 'Error');
    } finally {
        hideLoading();
    }
}

// ============================================
// EVENT LISTENERS & INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    updateWaktu();
    setInterval(updateWaktu, 1000);

    tampilkanKotaUser();

    const btnMasuk = document.getElementById('btn-absen-masuk');
    const btnPulang = document.getElementById('btn-absen-pulang');
    const btnRiwayat = document.getElementById('btn-riwayat');

    if (btnMasuk) btnMasuk.addEventListener('click', () => prosesAbsensi('masuk'));
    if (btnPulang) btnPulang.addEventListener('click', () => prosesAbsensi('pulang'));
    if (btnRiwayat) btnRiwayat.addEventListener('click', tampilkanRiwayat);

    const nipInput = document.getElementById('nip');
    if (nipInput) {
        nipInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
        nipInput.focus();
    }

    console.log('✅ Absensi main script loaded (FIXED VERSION)');
});
