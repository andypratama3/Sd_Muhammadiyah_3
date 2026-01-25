/**
 * Real-time Location Tracking System - FINAL FIXED VERSION
 * File: public/js/realtime-location.js
 *
 * FIXES:
 * - Better null handling for distanceFromLast
 * - Enhanced logging
 * - Optimized watchPosition settings
 */

class RealtimeLocationTracker {
    constructor(options = {}) {
        this.watchId = null;
        this.lastLocation = null;
        this.locationHistory = [];
        this.maxHistorySize = 50;
        this.updateInterval = options.updateInterval || 5000;
        this.minAccuracy = options.minAccuracy || 50;
        this.distanceThreshold = options.distanceThreshold || 10;
        this.geofenceStatus = null;
        this.isTracking = false;
        this.pauseTracking = false;
        this.onLocationUpdate = options.onLocationUpdate || null;
        this.onGeofenceChange = options.onGeofenceChange || null;
        this.onAccuracyWarning = options.onAccuracyWarning || null;


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



        navigator.geolocation.getCurrentPosition(
            position => {
                this.processLocationUpdate(position);

            },
            error => {

                this.handleLocationError(error);
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );


        this.watchId = navigator.geolocation.watchPosition(
            position => {
                if (!this.pauseTracking) {
                    this.processLocationUpdate(position);
                }
            },
            error => console.warn('⚠️ Watch position error:', error), {
                enableHighAccuracy: true,
                timeout: 8000,
                maximumAge: 0
            }
        );
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
            formattedTime: new Date(position.timestamp).toLocaleTimeString('id-ID'),
            distanceFromLast: null
        };


        if (currentLocation.accuracy > this.minAccuracy) {
            if (this.onAccuracyWarning) {
                this.onAccuracyWarning({
                    accuracy: currentLocation.accuracy,
                    threshold: this.minAccuracy
                });
            }
        }


        if (this.lastLocation) {
            const distance = this.calculateDistance(
                this.lastLocation.latitude,
                this.lastLocation.longitude,
                currentLocation.latitude,
                currentLocation.longitude
            );

            currentLocation.distanceFromLast = distance;


            if (distance < this.distanceThreshold &&
                currentLocation.accuracy >= this.lastLocation.accuracy) {

                return;
            }


            if (distance >= this.distanceThreshold) {
                console.log(`🚶 Movement detected: ${distance.toFixed(1)}m → triggering update`);
            }
        } else {


        }


        this.lastLocation = currentLocation;


        this.addToHistory(currentLocation);


        const oldGeofenceStatus = this.geofenceStatus;
        this.geofenceStatus = this.checkGeofenceStatus(
            currentLocation.latitude,
            currentLocation.longitude
        );


        if (oldGeofenceStatus !== null && oldGeofenceStatus !== this.geofenceStatus) {
            if (this.onGeofenceChange) {

                this.onGeofenceChange({
                    status: this.geofenceStatus,
                    previousStatus: oldGeofenceStatus,
                    latitude: currentLocation.latitude,
                    longitude: currentLocation.longitude,
                    timestamp: currentLocation.timestamp
                });
            }
        } else if (oldGeofenceStatus === null) {


            if (this.onGeofenceChange) {
                this.onGeofenceChange({
                    status: this.geofenceStatus,
                    previousStatus: null,
                    latitude: currentLocation.latitude,
                    longitude: currentLocation.longitude,
                    timestamp: currentLocation.timestamp
                });
            }
        }


        if (this.onLocationUpdate) {
            this.onLocationUpdate(currentLocation);
        }


        console.log(
            `📍 Update: ${currentLocation.latitude.toFixed(6)}, ${currentLocation.longitude.toFixed(6)} | ` +
            `±${currentLocation.accuracy.toFixed(0)}m | ` +
            `Dist: ${currentLocation.distanceFromLast ? currentLocation.distanceFromLast.toFixed(1) + 'm' : 'First'} | ` +
            `Geofence: ${this.geofenceStatus}`
        );
    }

    /**
     * Add location to history
     */
    addToHistory(location) {
        this.locationHistory.unshift(location);


        if (this.locationHistory.length > this.maxHistorySize) {
            this.locationHistory = this.locationHistory.slice(0, this.maxHistorySize);
        }
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
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

            if (current && previous) {
                const distance = this.calculateDistance(
                    current.latitude,
                    current.longitude,
                    previous.latitude,
                    previous.longitude
                );

                totalDistance += distance;
            }
        }

        return totalDistance;
    }

    /**
     * Handle location errors
     */
    handleLocationError(error) {
        let message = '';

        switch (error.code) {
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



        if (window.notify) {
            window.notify.warning(message, 'Lokasi');
        }
    }

    /**
     * Get statistics
     */
    getStatistics() {
        const totalDistance = this.getTotalDistance();
        const avgAccuracy = this.locationHistory.length > 0 ?
            this.locationHistory.reduce((sum, loc) => sum + (loc.accuracy || 0), 0) / this.locationHistory.length :
            0;

        const firstLocation = this.locationHistory[this.locationHistory.length - 1];
        const trackingDuration = (this.lastLocation && firstLocation) ?
            (this.lastLocation.timestamp - firstLocation.timestamp) / 1000 :
            0;

        return {
            totalLocations: this.locationHistory.length,
            totalDistance: totalDistance,
            avgAccuracy: avgAccuracy,
            currentGeofenceStatus: this.geofenceStatus,
            trackingDuration: trackingDuration
        };
    }
}





/**
 * Initialize global tracker - THIS IS THE KEY FUNCTION
 */
function initializeRealtimeTracker(options = {}) {







    const tracker = new RealtimeLocationTracker({
        updateInterval: 5000,
        minAccuracy: 50,
        distanceThreshold: 10,
        ...options,


        onLocationUpdate: (location) => {
            if (options.onLocationUpdate) {
                options.onLocationUpdate(location);
            }
        },

        onGeofenceChange: (data) => {
            if (options.onGeofenceChange) {
                options.onGeofenceChange(data);
            }
        },

        onAccuracyWarning: (warning) => {
            if (options.onAccuracyWarning) {
                options.onAccuracyWarning(warning);
            }
        }
    });


    return tracker;
}






window.RealtimeLocationTracker = RealtimeLocationTracker;


window.initializeRealtimeTracker = initializeRealtimeTracker;
