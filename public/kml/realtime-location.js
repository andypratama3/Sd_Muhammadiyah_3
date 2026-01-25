/**
 * Real-time Location Tracking System
 * File: public/js/realtime-location.js
 *
 * Features:
 * - Real-time position updates every 5 seconds
 * - Automatic map centering on user movement
 * - Location history tracking
 * - Movement distance calculation
 * - Geofence crossing detection
 * - Location accuracy monitoring
 */

class RealtimeLocationTracker {
    constructor(options = {}) {
        this.watchId = null;
        this.lastLocation = null;
        this.locationHistory = [];
        this.maxHistorySize = 50;
        this.updateInterval = options.updateInterval || 5000; // 5 seconds
        this.minAccuracy = options.minAccuracy || 50; // meters
        this.distanceThreshold = options.distanceThreshold || 10; // meters
        this.geofenceStatus = null;
        this.isTracking = false;
        this.onLocationUpdate = options.onLocationUpdate || null;
        this.onGeofenceChange = options.onGeofenceChange || null;
        this.onAccuracyWarning = options.onAccuracyWarning || null;

        // WebSocket untuk sync real-time ke server (optional)
        this.websocketEnabled = options.websocketEnabled || false;
        this.websocket = null;
        this.serverUrl = options.serverUrl || null;

        console.log('✅ RealtimeLocationTracker initialized');
    }

    /**
     * Start real-time location tracking
     */
    startTracking() {
        if (this.isTracking) {
            console.warn('⚠️ Tracking sudah aktif');
            return;
        }

        if (!navigator.geolocation) {
            console.error('❌ Geolocation tidak didukung');
            return;
        }

        this.isTracking = true;
        console.log('🚀 Starting real-time location tracking...');

        // Get initial position
        navigator.geolocation.getCurrentPosition(
            position => {
                this.processLocationUpdate(position);
                console.log('✅ Initial position obtained');
            },
            error => {
                console.error('❌ Initial position error:', error);
                this.handleLocationError(error);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );

        // Watch position untuk updates real-time
        this.watchId = navigator.geolocation.watchPosition(
            position => this.processLocationUpdate(position),
            error => console.warn('⚠️ Watch position error:', error),
            {
                enableHighAccuracy: true,
                timeout: 8000,
                maximumAge: 0
            }
        );

        // Initialize WebSocket jika enabled
        if (this.websocketEnabled && this.serverUrl) {
            this.initializeWebSocket();
        }
    }

    /**
     * Stop real-time location tracking
     */
    stopTracking() {
        if (this.watchId !== null) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }

        this.isTracking = false;

        if (this.websocket) {
            this.websocket.close();
        }

        console.log('🛑 Location tracking stopped');
    }

    /**
     * Process location update
     */
    processLocationUpdate(position) {
        const currentLocation = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            altitude: position.coords.altitude,
            altitudeAccuracy: position.coords.altitudeAccuracy,
            heading: position.coords.heading,
            speed: position.coords.speed,
            timestamp: position.timestamp,
            formattedTime: new Date(position.timestamp).toLocaleTimeString('id-ID')
        };

        // Cek accuracy warning
        if (currentLocation.accuracy > this.minAccuracy) {
            if (this.onAccuracyWarning) {
                this.onAccuracyWarning({
                    accuracy: currentLocation.accuracy,
                    threshold: this.minAccuracy
                });
            }
        }

        // Hitung jarak dari lokasi terakhir
        if (this.lastLocation) {
            const distance = this.calculateDistance(
                this.lastLocation.latitude,
                this.lastLocation.longitude,
                currentLocation.latitude,
                currentLocation.longitude
            );

            currentLocation.distanceFromLast = distance;

            // Hanya update jika pergerakan signifikan atau accuracy lebih baik
            if (distance < this.distanceThreshold &&
                currentLocation.accuracy >= this.lastLocation.accuracy) {
                console.log(`⏭️ Skipping update: jarak ${distance.toFixed(1)}m < threshold ${this.distanceThreshold}m`);
                return;
            }
        }

        // Update last location
        this.lastLocation = currentLocation;

        // Add to history
        this.addToHistory(currentLocation);

        // Check geofence status
        const oldGeofenceStatus = this.geofenceStatus;
        this.geofenceStatus = this.checkGeofenceStatus(
            currentLocation.latitude,
            currentLocation.longitude
        );

        // Trigger geofence change event
        if (oldGeofenceStatus !== this.geofenceStatus && this.onGeofenceChange) {
            this.onGeofenceChange({
                status: this.geofenceStatus,
                latitude: currentLocation.latitude,
                longitude: currentLocation.longitude,
                timestamp: currentLocation.timestamp
            });
        }

        // Trigger location update callback
        if (this.onLocationUpdate) {
            this.onLocationUpdate(currentLocation);
        }

        // Send to server via WebSocket
        if (this.websocket && this.websocket.readyState === WebSocket.OPEN) {
            this.sendLocationToServer(currentLocation);
        }

        console.log(`📍 Location updated: ${currentLocation.latitude.toFixed(6)}, ${currentLocation.longitude.toFixed(6)} (±${currentLocation.accuracy.toFixed(0)}m)`);
    }

    /**
     * Add location to history
     */
    addToHistory(location) {
        this.locationHistory.unshift(location);

        // Keep only recent locations
        if (this.locationHistory.length > this.maxHistorySize) {
            this.locationHistory = this.locationHistory.slice(0, this.maxHistorySize);
        }
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Earth radius in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c; // Distance in meters
    }

    /**
     * Check if location is inside geofence (polygon)
     */
    checkGeofenceStatus(lat, lon) {
        if (!window.polygonLayers || window.polygonLayers.length === 0) {
            return 'unknown';
        }

        const point = L.latLng(lat, lon);

        for (let layer of window.polygonLayers) {
            const bounds = layer.getBounds();
            if (bounds.contains(point)) {
                const polygon = layer.getLatLngs()[0];
                if (this.isPointInPolygon(point, polygon)) {
                    return 'inside';
                }
            }
        }

        return 'outside';
    }

    /**
     * Ray casting algorithm untuk point-in-polygon check
     */
    isPointInPolygon(point, polygon) {
        let inside = false;
        const x = point.lat;
        const y = point.lng;

        for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
            const xi = polygon[i].lat;
            const yi = polygon[i].lng;
            const xj = polygon[j].lat;
            const yj = polygon[j].lng;

            const intersect = ((yi > y) !== (yj > y)) &&
                (x < (xj - xi) * (y - yi) / (yj - yi) + xi);

            if (intersect) inside = !inside;
        }

        return inside;
    }

    /**
     * Get current location
     */
    getCurrentLocation() {
        return this.lastLocation;
    }

    /**
     * Get location history
     */
    getLocationHistory() {
        return this.locationHistory;
    }

    /**
     * Get total distance traveled
     */
    getTotalDistance() {
        let totalDistance = 0;

        for (let i = 0; i < this.locationHistory.length - 1; i++) {
            const current = this.locationHistory[i];
            const previous = this.locationHistory[i + 1];

            const distance = this.calculateDistance(
                current.latitude,
                current.longitude,
                previous.latitude,
                previous.longitude
            );

            totalDistance += distance;
        }

        return totalDistance;
    }

    /**
     * Initialize WebSocket connection
     */
    initializeWebSocket() {
        if (!this.serverUrl) return;

        try {
            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            this.websocket = new WebSocket(`${protocol}//${this.serverUrl}/location-tracking`);

            this.websocket.onopen = () => {
                console.log('✅ WebSocket connected for real-time location sync');
            };

            this.websocket.onmessage = (event) => {
                console.log('📨 WebSocket message:', event.data);
            };

            this.websocket.onerror = (error) => {
                console.error('❌ WebSocket error:', error);
            };

            this.websocket.onclose = () => {
                console.log('🔌 WebSocket disconnected');
                // Reconnect setelah 5 detik
                if (this.isTracking) {
                    setTimeout(() => this.initializeWebSocket(), 5000);
                }
            };
        } catch (error) {
            console.error('❌ WebSocket initialization error:', error);
        }
    }

    /**
     * Send location to server
     */
    sendLocationToServer(location) {
        if (!this.websocket || this.websocket.readyState !== WebSocket.OPEN) {
            return;
        }

        this.websocket.send(JSON.stringify({
            type: 'location_update',
            data: {
                latitude: location.latitude,
                longitude: location.longitude,
                accuracy: location.accuracy,
                timestamp: location.timestamp,
                geofenceStatus: this.geofenceStatus
            }
        }));
    }

    /**
     * Handle location errors
     */
    handleLocationError(error) {
        let message = '';

        switch(error.code) {
            case 1:
                message = 'Akses lokasi ditolak. Mohon izinkan di pengaturan.';
                break;
            case 2:
                message = 'Informasi lokasi tidak tersedia.';
                break;
            case 3:
                message = 'Waktu permintaan lokasi habis.';
                break;
            default:
                message = 'Terjadi kesalahan tidak dikenal.';
        }

        console.error('❌ Location error:', message);

        if (window.notify) {
            window.notify.warning(message, 'Lokasi');
        }
    }

    /**
     * Get statistics
     */
    getStatistics() {
        const totalDistance = this.getTotalDistance();
        const avgAccuracy = this.locationHistory.length > 0
            ? this.locationHistory.reduce((sum, loc) => sum + loc.accuracy, 0) / this.locationHistory.length
            : 0;

        return {
            totalLocations: this.locationHistory.length,
            totalDistance: totalDistance,
            avgAccuracy: avgAccuracy,
            currentGeofenceStatus: this.geofenceStatus,
            trackingDuration: this.lastLocation
                ? (this.lastLocation.timestamp - this.locationHistory[this.locationHistory.length - 1].timestamp) / 1000
                : 0
        };
    }
}

/**
 * Initialize global tracker
 */
let realtimeTracker = null;

function initializeRealtimeTracker(options = {}) {
    realtimeTracker = new RealtimeLocationTracker({
        updateInterval: 5000,
        minAccuracy: 50,
        distanceThreshold: 10,
        websocketEnabled: false, // Set to true untuk enable WebSocket
        ...options,

        // Callback handlers
        onLocationUpdate: (location) => {
            updateMapWithRealtimeLocation(location);
        },

        onGeofenceChange: (data) => {
            handleGeofenceChange(data);
        },

        onAccuracyWarning: (warning) => {
            if (window.notify) {
                window.notify.warning(
                    `Akurasi lokasi: ±${warning.accuracy.toFixed(0)}m (threshold: ±${warning.threshold}m)`,
                    'Akurasi GPS'
                );
            }
        }
    });

    return realtimeTracker;
}

/**
 * Update map with real-time location
 */
function updateMapWithRealtimeLocation(location) {
    if (!window.map || !window.userMarker) return;

    // Update marker position
    window.userMarker.setLatLng([location.latitude, location.longitude]);

    // Update accuracy circle
    if (window.accuracyCircle) {
        window.map.removeLayer(window.accuracyCircle);
    }

    window.accuracyCircle = L.circle([location.latitude, location.longitude], {
        radius: location.accuracy,
        color: '#4299e1',
        fillColor: '#4299e1',
        fillOpacity: 0.1,
        weight: 1,
        dashArray: '5, 5'
    }).addTo(window.map);

    // Auto pan ke user jika setting enabled
    if (window.autoPanToUser !== false) {
        window.map.panTo([location.latitude, location.longitude], {
            animate: true,
            duration: 1
        });
    }

    // Update popup
    const isInside = realtimeTracker.geofenceStatus === 'inside';
    window.userMarker.setPopupContent(`
        <div style="text-align: center; padding: 5px;">
            <strong><i class="fas fa-map-marker-alt" style="color: #48bb78;"></i> Lokasi Anda</strong><br>
            <hr style="margin: 5px 0;">
            <small><strong>Koordinat:</strong></small><br>
            <small>Lat: ${location.latitude.toFixed(6)}</small><br>
            <small>Lon: ${location.longitude.toFixed(6)}</small><br>
            <small class="text-muted">Akurasi: ±${location.accuracy.toFixed(0)}m</small><br>
            <small class="text-muted">Update: ${location.formattedTime}</small><br>
            ${isInside ?
                '<span style="color: #48bb78;"><i class="fas fa-check-circle"></i> Dalam area</span>' :
                '<span style="color: #f56565;"><i class="fas fa-times-circle"></i> Di luar area</span>'
            }
        </div>
    `);
}

/**
 * Handle geofence status change
 */
function handleGeofenceChange(data) {
    console.log(`🚨 Geofence status changed: ${data.status}`);

    if (window.updateMapStatusBadge) {
        window.updateMapStatusBadge(data.status === 'inside');
    }

    if (window.notify) {
        if (data.status === 'inside') {
            window.notify.success('Anda memasuki area absensi', 'Geofence');
        } else if (data.status === 'outside') {
            window.notify.warning('Anda keluar dari area absensi', 'Geofence');
        }
    }
}

// Export untuk digunakan
window.RealtimeLocationTracker = RealtimeLocationTracker;
window.initializeRealtimeTracker = initializeRealtimeTracker;
