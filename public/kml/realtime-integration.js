/**
 * Real-time Location Integration Script
 * File: public/js/realtime-integration.js
 *
 * Script ini mengintegrasikan RealtimeLocationTracker dengan sistem absensi
 * yang sudah ada. Pastikan script ini di-load setelah realtime-location.js
 */

// ============================================
// INTEGRATION INITIALIZATION
// ============================================

/**
 * Setup real-time tracking saat DOM ready
 */
document.addEventListener('DOMContentLoaded', function() {
    // Wait untuk map sudah siap
    setTimeout(() => {
        if (window.map && window.initializeRealtimeTracker) {
            setupRealtimeLocationTracking();
        }
    }, 2000);
});

/**
 * Setup real-time location tracking
 */
function setupRealtimeLocationTracking() {
    console.log('🔧 Setting up real-time location tracking...');

    // Initialize tracker dengan config default
    window.realtimeTracker = window.initializeRealtimeTracker({
        updateInterval: 5000,              // Update setiap 5 detik
        minAccuracy: 50,                   // Warning jika akurasi > 50m
        distanceThreshold: 10,             // Minimum 10m pergerakan untuk update
        websocketEnabled: false,           // Disable WebSocket by default
        serverUrl: null,

        // Callback saat lokasi berubah
        onLocationUpdate: (location) => {
            handleLocationUpdate(location);
        },

        // Callback saat masuk/keluar geofence
        onGeofenceChange: (data) => {
            handleGeofenceChange(data);
        },

        // Callback saat akurasi kurang baik
        onAccuracyWarning: (warning) => {
            handleAccuracyWarning(warning);
        }
    });

    // Start tracking
    window.realtimeTracker.startTracking();
    console.log('✅ Real-time tracking started');

    // Setup UI updates
    setupRealtimeUI();

    // Setup auto-cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (window.realtimeTracker) {
            window.realtimeTracker.stopTracking();
        }
    });
}

// ============================================
// LOCATION UPDATE HANDLER
// ============================================

/**
 * Handle real-time location update
 */
function handleLocationUpdate(location) {
    // Update map marker dan accuracy circle
    if (window.map && window.userMarker) {
        // Set new position
        window.userMarker.setLatLng([location.latitude, location.longitude]);

        // Update accuracy circle
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

        // Auto pan ke lokasi user (smooth animation)
        if (window.autoPanToUser !== false) {
            window.map.panTo([location.latitude, location.longitude], {
                animate: true,
                duration: 1
            });
        }

        // Update marker popup
        updateUserMarkerPopup(location);
    }

    // Update real-time info panel
    updateRealtimeInfoPanel(location);

    // Log untuk debugging
    console.log(`📍 ${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)} (±${location.accuracy.toFixed(0)}m)`);
}

/**
 * Update user marker popup
 */
function updateUserMarkerPopup(location) {
    if (!window.userMarker) return;

    const isInside = window.realtimeTracker.geofenceStatus === 'inside';
    const statusText = isInside
        ? '<span style="color: #48bb78;"><i class="fas fa-check-circle"></i> Dalam area</span>'
        : '<span style="color: #f56565;"><i class="fas fa-times-circle"></i> Di luar area</span>';

    window.userMarker.setPopupContent(`
        <div style="text-align: center; padding: 8px; font-size: 12px;">
            <strong><i class="fas fa-map-marker-alt" style="color: #48bb78;"></i> Lokasi Anda</strong>
            <hr style="margin: 5px 0;">
            <strong>Koordinat:</strong><br>
            <small>Lat: ${location.latitude.toFixed(6)}</small><br>
            <small>Lon: ${location.longitude.toFixed(6)}</small><br>
            <small class="text-muted">Akurasi: ±${location.accuracy.toFixed(0)}m</small><br>
            <small class="text-muted">Update: ${location.formattedTime}</small><br>
            ${location.speed !== null ? `<small class="text-muted">Kecepatan: ${(location.speed * 3.6).toFixed(1)} km/h</small><br>` : ''}
            ${statusText}
        </div>
    `);
}

/**
 * Update real-time info panel
 */
function updateRealtimeInfoPanel(location) {
    const panel = document.getElementById('realtime-info-panel');
    if (!panel) return;

    const isInside = window.realtimeTracker.geofenceStatus === 'inside';
    const stats = window.realtimeTracker.getStatistics();

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
                <span style="color: ${isInside ? '#48bb78' : '#f56565'};">
                    ${isInside ? '✓ Dalam area' : '✗ Di luar area'}
                </span>
            </div>
            <hr>
            <div class="info-row">
                <strong>📊 Total Jarak:</strong>
                <span>${stats.totalDistance.toFixed(0)}m</span>
            </div>
            <div class="info-row">
                <strong>📈 Rata-rata Akurasi:</strong>
                <span>±${stats.avgAccuracy.toFixed(0)}m</span>
            </div>
            <div class="info-row">
                <strong>📍 Lokasi Tercatat:</strong>
                <span>${stats.totalLocations}</span>
            </div>
        </div>
    `;
}

// ============================================
// GEOFENCE CHANGE HANDLER
// ============================================

/**
 * Handle geofence status change
 */
function handleGeofenceChange(data) {
    console.log(`🚨 Geofence status: ${data.status}`);

    // Update map status badge
    if (window.updateMapStatusBadge) {
        window.updateMapStatusBadge(data.status === 'inside');
    }

    // Show notification
    if (window.notify) {
        if (data.status === 'inside') {
            window.notify.success(
                'Anda telah memasuki area absensi',
                'Geofence'
            );
        } else if (data.status === 'outside') {
            window.notify.warning(
                'Anda telah keluar dari area absensi',
                'Geofence'
            );
        }
    }

    // Disable/enable buttons based on geofence status
    updateAbsensiButtons(data.status === 'inside');

}

/**
 * Enable/disable absensi buttons berdasarkan geofence
 */
function updateAbsensiButtons(isInsideGeofence) {
    const btnMasuk = document.getElementById('btn-absen-masuk');
    const btnPulang = document.getElementById('btn-absen-pulang');

    if (btnMasuk) {
        btnMasuk.disabled = !isInsideGeofence;
        btnMasuk.title = isInsideGeofence
            ? 'Klik untuk absen masuk'
            : 'Anda harus berada dalam area absensi';
    }

    if (btnPulang) {
        btnPulang.disabled = !isInsideGeofence;
        btnPulang.title = isInsideGeofence
            ? 'Klik untuk absen pulang'
            : 'Anda harus berada dalam area absensi';
    }
}


// ============================================
// ACCURACY WARNING HANDLER
// ============================================

/**
 * Handle accuracy warning
 */
function handleAccuracyWarning(warning) {
    console.warn(
        `⚠️ GPS Accuracy Warning: ±${warning.accuracy.toFixed(0)}m (threshold: ±${warning.threshold}m)`
    );

    if (window.notify) {
        window.notify.warning(
            `Akurasi GPS kurang baik: ±${warning.accuracy.toFixed(0)}m. ` +
            `Pastikan di area outdoor dengan sinyal GPS kuat.`,
            'Akurasi GPS'
        );
    }
}

// ============================================
// UI SETUP
// ============================================

/**
 * Setup real-time UI elements
 */
function setupRealtimeUI() {
    // Check if realtime info panel exists, jika tidak create
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

    // Add CSS styles jika belum ada
    addRealtimeStyles();

    // Add control buttons
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

        .realtime-control-button:hover {
            background: #3182ce;
        }

        .realtime-control-button.stop {
            background: #f56565;
        }

        .realtime-control-button.stop:hover {
            background: #e53e3e;
        }
    `;

    document.head.appendChild(style);
}

/**
 * Add control buttons untuk real-time tracking
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
        <button class="realtime-control-button" id="btn-resume-tracking">
            <i class="fas fa-play"></i> Resume
        </button>
        <button class="realtime-control-button stop" id="btn-stop-tracking">
            <i class="fas fa-stop"></i> Stop
        </button>
    `;

    panel.appendChild(controls);

    // Event listeners
    document.getElementById('btn-pause-tracking')?.addEventListener('click', () => {
        if (window.realtimeTracker) {
            window.realtimeTracker.pauseTracking = true;
            window.notify?.info('Real-time tracking dipause', 'Tracking');
        }
    });

    document.getElementById('btn-resume-tracking')?.addEventListener('click', () => {
        if (window.realtimeTracker) {
            window.realtimeTracker.pauseTracking = false;
            window.notify?.info('Real-time tracking dilanjutkan', 'Tracking');
        }
    });

    document.getElementById('btn-stop-tracking')?.addEventListener('click', () => {
        if (window.realtimeTracker) {
            window.realtimeTracker.stopTracking();
            window.notify?.warning('Real-time tracking dihentikan', 'Tracking');
        }
    });
}

// ============================================
// UTILITIES
// ============================================

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

/**
 * Export tracking data
 */
function exportTrackingData() {
    const status = getTrackingStatus();
    const data = {
        exportTime: new Date().toISOString(),
        tracking: status,
        history: window.realtimeTracker?.getLocationHistory() || []
    };

    return JSON.stringify(data, null, 2);
}

/**
 * Get tracking summary
 */
function getTrackingSummary() {
    const stats = window.realtimeTracker?.getStatistics();
    if (!stats) return null;

    return {
        totalLocationsRecorded: stats.totalLocations,
        totalDistance: `${stats.totalDistance.toFixed(0)}m`,
        averageAccuracy: `±${stats.avgAccuracy.toFixed(0)}m`,
        currentGeofenceStatus: stats.currentGeofenceStatus,
        trackingDuration: `${stats.trackingDuration.toFixed(0)}s`
    };
}

// Export functions globally
window.getTrackingStatus = getTrackingStatus;
window.exportTrackingData = exportTrackingData;
window.getTrackingSummary = getTrackingSummary;

console.log('✅ Real-time integration script loaded');
