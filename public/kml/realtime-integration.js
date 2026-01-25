/**
 * Real-time Location Integration Script - FINAL FIXED VERSION
 * File: public/js/realtime-integration.js
 *
 * FIXES:
 * - Changed distanceThreshold from 5m to 10m (optimal)
 * - Enhanced logging with distance info
 * - Better null checks
 * - Optimized debounce timings
 */





/**
 * Debounce function to prevent rapid updates
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}




let lastGeofenceStatus = null;
let lastNotificationTime = 0;
const NOTIFICATION_COOLDOWN = 3000;





/**
 * Setup real-time tracking saat map ready
 */
document.addEventListener('DOMContentLoaded', function() {



    window.addEventListener('mapReady', function(event) {



        setTimeout(() => {
            setupRealtimeLocationTracking();
        }, 500);
    });


    setTimeout(() => {
        if (window.mapReady && !window.realtimeTracker) {
            console.log('🔄 Map already ready, starting tracker (fallback 1)...');
            setupRealtimeLocationTracking();
        }
    }, 3000);


    setTimeout(() => {
        if (!window.realtimeTracker) {



            if (window.map && typeof window.initializeRealtimeTracker === 'function') {

                setupRealtimeLocationTracking();
            } else {
                console.error('❌ Cannot initialize tracker - missing dependencies');
                window.notify?.error(
                    'Gagal menginisialisasi sistem tracking. Silakan refresh halaman.',
                    'Error Sistem'
                );
            }
        }
    }, 5000);
});

/**
 * Setup real-time location tracking
 */
function setupRealtimeLocationTracking() {
    if (window.realtimeTracker) {

        return;
    }


    if (typeof window.initializeRealtimeTracker !== 'function') {


        window.notify?.error(
            'Sistem tracking belum siap. Silakan refresh halaman.',
            'Error Inisialisasi'
        );
        return;
    }

    if (!window.map) {

        return;
    }




    window.realtimeTracker = window.initializeRealtimeTracker({
        updateInterval: 5000,
        minAccuracy: 50,
        distanceThreshold: 10,
        websocketEnabled: false,
        serverUrl: null,


        onLocationUpdate: debounce((location) => {
            handleLocationUpdate(location);
        }, 800),


        onGeofenceChange: debounce((data) => {
            handleGeofenceChange(data);
        }, 1500),


        onAccuracyWarning: debounce((warning) => {
            handleAccuracyWarning(warning);
        }, 5000)
    });


    window.realtimeTracker.startTracking();



    setupRealtimeUI();


    window.addEventListener('beforeunload', () => {
        if (window.realtimeTracker) {
            window.realtimeTracker.stopTracking();
        }
    });
}





/**
 * Handle real-time location update
 */
function handleLocationUpdate(location) {

    if (window.map && !window.userMarker) {
        if (window.createInitialUserMarker) {
            window.createInitialUserMarker(
                location.latitude,
                location.longitude,
                location.accuracy
            );
        }
    }


    if (window.map && window.userMarker) {

        window.userMarker.setLatLng([location.latitude, location.longitude]);


        if (window.accuracyCircle) {
            window.map.removeLayer(window.accuracyCircle);
        }

        window.accuracyCircle = L.circle(
            [location.latitude, location.longitude],
            {
                radius: location.accuracy,
                color: '#4299e1',
                fillColor: '#4299e1',
                fillOpacity: 0.1,
                weight: 1,
                dashArray: '5, 5'
            }
        ).addTo(window.map);


        if (location.distanceFromLast && location.distanceFromLast > 20) {
            if (window.autoPanToUser !== false) {
                window.map.panTo([location.latitude, location.longitude], {
                    animate: true,
                    duration: 1,
                    easeLinearity: 0.5
                });
            }
        }


        updateUserMarkerPopup(location);
    }


    updateRealtimeInfoPanel(location);


    const logFrequency = 0.3;
    if (Math.random() < logFrequency) {
        console.log(
            `📍 Pos: ${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)} | ` +
            `Akurasi: ±${location.accuracy.toFixed(0)}m | ` +
            `Jarak: ${location.distanceFromLast ? location.distanceFromLast.toFixed(1) + 'm' : 'N/A'} | ` +
            `Status: ${window.realtimeTracker.geofenceStatus || 'unknown'}`
        );
    }
}

/**
 * Update user marker popup (debounced internally)
 */
const updateUserMarkerPopup = debounce(function(location) {
    if (!window.userMarker) return;

    const isInside = window.realtimeTracker?.geofenceStatus === 'inside';
    const statusText = isInside
        ? '<span style="color: #48bb78;"><i class="fas fa-check-circle"></i> Dalam area</span>'
        : '<span style="color: #f56565;"><i class="fas fa-times-circle"></i> Di luar area</span>';

    const popupContent = `
        <div style="text-align: center; padding: 8px; font-size: 12px;">
            <strong><i class="fas fa-map-marker-alt" style="color: #48bb78;"></i> Lokasi Anda</strong>
            <hr style="margin: 5px 0;">
            <strong>Koordinat:</strong><br>
            <small>Lat: ${location.latitude.toFixed(6)}</small><br>
            <small>Lon: ${location.longitude.toFixed(6)}</small><br>
            <small class="text-muted">Akurasi: ±${location.accuracy.toFixed(0)}m</small><br>
            <small class="text-muted">Update: ${location.formattedTime}</small><br>
            ${location.distanceFromLast ? `<small class="text-info">Jarak: ${location.distanceFromLast.toFixed(1)}m</small><br>` : ''}
            ${location.speed !== null && location.speed > 0.5 ? `<small class="text-muted">Kecepatan: ${(location.speed * 3.6).toFixed(1)} km/h</small><br>` : ''}
            ${statusText}
        </div>
    `;

    window.userMarker.setPopupContent(popupContent);
}, 1500);

/**
 * Update real-time info panel
 */
function updateRealtimeInfoPanel(location) {
    const panel = document.getElementById('realtime-info-panel');
    if (!panel) return;

    const isInside = window.realtimeTracker?.geofenceStatus === 'inside';
    const stats = window.realtimeTracker?.getStatistics() || {
        totalDistance: 0,
        avgAccuracy: 0,
        totalLocations: 0
    };

    panel.innerHTML = `
        <div class="realtime-info-content">
            <div class="info-row">
                <strong>📍 Latitude:</strong>
                <span>${location.latitude.toFixed(6)}</span>
            </div>
            <div class="info-row">
                <strong>📍 Longitude:</strong>
                <span>${location.longitude.toFixed(6)}</span>
            </div>
            <div class="info-row">
                <strong>📡 Akurasi:</strong>
                <span>±${location.accuracy.toFixed(0)}m</span>
            </div>
            <div class="info-row">
                <strong>⏱️ Update:</strong>
                <span>${location.formattedTime}</span>
            </div>
            <div class="info-row">
                <strong>🎯 Status:</strong>
                <span style="color: ${isInside ? '#48bb78' : '#f56565'}; font-weight: bold;">
                    ${isInside ? '✓ Dalam area' : '✗ Di luar area'}
                </span>
            </div>
            <hr style="margin: 8px 0; border-color: #e2e8f0;">
            <div class="info-row">
                <strong>📊 Total Jarak:</strong>
                <span>${stats.totalDistance.toFixed(0)}m</span>
            </div>
            <div class="info-row">
                <strong>📈 Rata² Akurasi:</strong>
                <span>±${stats.avgAccuracy.toFixed(0)}m</span>
            </div>
            <div class="info-row">
                <strong>📍 Update Count:</strong>
                <span>${stats.totalLocations}</span>
            </div>
        </div>
    `;
}





/**
 * Handle geofence status change (with duplicate prevention)
 */
function handleGeofenceChange(data) {
    const now = Date.now();


    if (lastGeofenceStatus === data.status) {
        console.log(`⏭️ Skipping duplicate geofence notification: ${data.status}`);
        return;
    }


    if (now - lastNotificationTime < NOTIFICATION_COOLDOWN) {
        console.log(`⏭️ Geofence notification on cooldown (${((now - lastNotificationTime) / 1000).toFixed(1)}s ago)`);
        return;
    }

    console.log(`🚨 Geofence status changed: ${lastGeofenceStatus || 'null'} → ${data.status}`);


    lastGeofenceStatus = data.status;
    lastNotificationTime = now;


    if (window.updateMapStatusBadge) {
        window.updateMapStatusBadge(data.status === 'inside');
    }


    if (window.notify && data.status !== 'unknown') {
        if (data.status === 'inside') {
            window.notify.success(
                'Anda telah memasuki area absensi. Tombol absensi telah diaktifkan.',
                '✅ Masuk Area'
            );
        } else if (data.status === 'outside') {
            window.notify.warning(
                'Anda telah keluar dari area absensi. Tombol absensi dinonaktifkan.',
                '⚠️ Keluar Area'
            );
        }
    }


    updateAbsensiButtons(data.status === 'inside');
}

/**
 * Enable/disable absensi buttons berdasarkan geofence (PERSISTENT)
 */
function updateAbsensiButtons(isInsideGeofence) {
    const btnMasuk = document.getElementById('btn-absen-masuk');
    const btnPulang = document.getElementById('btn-absen-pulang');

    if (btnMasuk) {
        btnMasuk.disabled = !isInsideGeofence;
        btnMasuk.title = isInsideGeofence
            ? 'Klik untuk absen masuk'
            : 'Anda harus berada dalam area absensi';


        if (isInsideGeofence) {
            btnMasuk.classList.remove('btn-disabled');
        } else {
            btnMasuk.classList.add('btn-disabled');
        }
    }

    if (btnPulang) {
        btnPulang.disabled = !isInsideGeofence;
        btnPulang.title = isInsideGeofence
            ? 'Klik untuk absen pulang'
            : 'Anda harus berada dalam area absensi';


        if (isInsideGeofence) {
            btnPulang.classList.remove('btn-disabled');
        } else {
            btnPulang.classList.add('btn-disabled');
        }
    }

    console.log(`🔘 Buttons ${isInsideGeofence ? 'enabled ✅' : 'disabled ❌'}`);
}





/**
 * Handle accuracy warning (already debounced in setup)
 */
function handleAccuracyWarning(warning) {
    console.warn(
        `⚠️ GPS Accuracy Warning: ±${warning.accuracy.toFixed(0)}m (threshold: ±${warning.threshold}m)`
    );

    if (window.notify) {
        window.notify.warning(
            `Akurasi GPS: ±${warning.accuracy.toFixed(0)}m. ` +
            `Untuk akurasi terbaik, pastikan berada di area outdoor dengan sinyal GPS kuat.`,
            'Info GPS'
        );
    }
}





/**
 * Setup real-time UI elements
 */
function setupRealtimeUI() {

    let panel = document.getElementById('realtime-info-panel');

    if (!panel) {
        panel = document.createElement('div');
        panel.id = 'realtime-info-panel';
        panel.className = 'realtime-info-panel';

        const mapContainer = document.getElementById('map-container');
        if (mapContainer && mapContainer.parentNode) {
            mapContainer.parentNode.insertBefore(panel, mapContainer.nextSibling);
        }
    }


    addRealtimeStyles();


    addRealtimeControls();
}

/**
 * Add CSS styles untuk real-time panel
 */
function addRealtimeStyles() {
    if (document.getElementById('realtime-styles')) return;

    const style = document.createElement('style');
    style.id = 'realtime-styles';
    style.textContent = `
        .realtime-info-panel {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            font-size: 13px;
        }

        .realtime-info-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row strong {
            color: #2d3748;
        }

        .info-row span {
            color: #4a5568;
            font-family: 'Courier New', monospace;
        }

        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed !important;
        }

        @media (max-width: 768px) {
            .realtime-info-content {
                grid-template-columns: 1fr;
            }
        }

        .realtime-control-button {
            padding: 6px 12px;
            margin: 4px;
            font-size: 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            background: #4299e1;
            color: white;
            transition: all 0.3s ease;
        }

        .realtime-control-button:hover:not(:disabled) {
            background: #3182ce;
        }

        .realtime-control-button.stop {
            background: #f56565;
        }

        .realtime-control-button.stop:hover:not(:disabled) {
            background: #e53e3e;
        }

        .realtime-control-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    `;

    document.head.appendChild(style);
}

/**
 * Add control buttons
 */
function addRealtimeControls() {
    const panel = document.getElementById('realtime-info-panel');
    if (!panel || panel.querySelector('.realtime-controls')) return;

    const controls = document.createElement('div');
    controls.className = 'realtime-controls';
    controls.style.marginTop = '12px';
    controls.style.borderTop = '1px solid #e2e8f0';
    controls.style.paddingTop = '12px';
    controls.innerHTML = `
        <button class="realtime-control-button" id="btn-pause-tracking">
            <i class="fas fa-pause"></i> Pause
        </button>
        <button class="realtime-control-button" id="btn-resume-tracking" disabled>
            <i class="fas fa-play"></i> Resume
        </button>
        <button class="realtime-control-button stop" id="btn-stop-tracking">
            <i class="fas fa-stop"></i> Stop
        </button>
    `;

    panel.appendChild(controls);


    const btnPause = document.getElementById('btn-pause-tracking');
    const btnResume = document.getElementById('btn-resume-tracking');
    const btnStop = document.getElementById('btn-stop-tracking');

    btnPause?.addEventListener('click', () => {
        if (window.realtimeTracker) {
            window.realtimeTracker.pauseTracking = true;
            btnPause.disabled = true;
            btnResume.disabled = false;
            window.notify?.info('Real-time tracking dipause', 'Tracking');
        }
    });

    btnResume?.addEventListener('click', () => {
        if (window.realtimeTracker) {
            window.realtimeTracker.pauseTracking = false;
            btnPause.disabled = false;
            btnResume.disabled = true;
            window.notify?.info('Real-time tracking dilanjutkan', 'Tracking');
        }
    });

    btnStop?.addEventListener('click', () => {
        if (window.realtimeTracker) {
            window.realtimeTracker.stopTracking();
            btnPause.disabled = true;
            btnResume.disabled = true;
            btnStop.disabled = true;
            window.notify?.warning('Real-time tracking dihentikan', 'Tracking');
        }
    });
}





/**
 * Get current tracking status
 */
function getTrackingStatus() {
    if (!window.realtimeTracker) {
        return { status: 'not_initialized' };
    }

    return {
        status: window.realtimeTracker.isTracking ? 'active' : 'inactive',
        currentLocation: window.realtimeTracker.getCurrentLocation(),
        geofenceStatus: window.realtimeTracker.geofenceStatus,
        statistics: window.realtimeTracker.getStatistics()
    };
}


window.getTrackingStatus = getTrackingStatus;
window.handleGeofenceChange = handleGeofenceChange;
window.updateAbsensiButtons = updateAbsensiButtons;


