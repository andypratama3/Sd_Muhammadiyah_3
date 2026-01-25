/**
 * Map & Location Management
 * File: public/js/absensi-map.js
 */

// ============================================
// GLOBAL VARIABLES
// ============================================

let map = null;
let userMarker = null;
let accuracyCircle = null;
let polygonLayers = [];
let watchId = null;

// ============================================
// MAP INITIALIZATION
// ============================================

/**
 * Initialize Leaflet map
 */
function initializeMap() {
    // Create map centered on SD Muhammadiyah 3 Samarinda
    map = L.map('map-container', {
        center: [-0.5093246, 117.1298813],
        zoom: 17,
        zoomControl: true,
        attributionControl: true
    });

    // Add OpenStreetMap tiles
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
        minZoom: 10,
        crossOrigin: true
    }).addTo(map);

    // Add scale control
    L.control.scale({
        imperial: false,
        metric: true
    }).addTo(map);

    // Force map to recalculate size
    setTimeout(() => {
        if (map) {
            map.invalidateSize();
            console.log('🔄 Map size invalidated');
        }
    }, 100);

    // Load KML polygon
    loadKmlPolygon();

    // Get and show user location
    getUserLocationForMap();

    console.log('🗺️ Map initialized');
}

// ============================================
// KML POLYGON LOADING
// ============================================

/**
 * Load KML polygon from server
 */
async function loadKmlPolygon() {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch('/kml/data', {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();

        if (!result.success) {
            console.warn('KML data tidak tersedia:', result.message);
            notify.warning('Area absensi belum dikonfigurasi', 'Info Peta');
            return;
        }

        const polygons = result.polygons || [];

        if (polygons.length === 0) {
            notify.info('Tidak ada polygon yang dikonfigurasi', 'Info Peta');
            return;
        }

        // Clear existing polygons
        polygonLayers.forEach(layer => map.removeLayer(layer));
        polygonLayers = [];

        // Draw each polygon
        polygons.forEach((polygon, index) => {
            const coordinates = polygon.coordinates.map(coord => [coord.lat, coord.lon]);

            if (coordinates.length < 3) {
                console.warn(`Polygon ${index} memiliki koordinat tidak valid`);
                return;
            }

            // Create polygon layer
            const polygonLayer = L.polygon(coordinates, {
                color: '#4299e1',
                fillColor: '#4299e1',
                fillOpacity: 0.3,
                weight: 3,
                opacity: 0.8
            }).addTo(map);

            // Add popup
            polygonLayer.bindPopup(`
                <div style="text-align: center; padding: 5px;">
                    <strong><i class="fas fa-school"></i> ${escapeHtml(polygon.name)}</strong><br>
                    <small class="text-muted">${coordinates.length} titik koordinat</small><br>
                    <small class="text-muted">Polygon ${index + 1} dari ${polygons.length}</small>
                </div>
            `, { maxWidth: 200 });

            polygonLayers.push(polygonLayer);

            console.log(`✅ Polygon ${index + 1} loaded: ${polygon.name}`);
        });

        // Fit map to show all polygons
        if (polygonLayers.length > 0) {
            const group = L.featureGroup(polygonLayers);
            map.fitBounds(group.getBounds().pad(0.1));
        }

        notify.success(`${polygons.length} area berhasil dimuat`, 'Peta Siap');

    } catch (error) {
        console.error('❌ Error loading KML:', error);
        notify.error('Gagal memuat area absensi: ' + error.message, 'Error Peta');
    }
}

// ============================================
// USER LOCATION TRACKING
// ============================================

/**
 * Get user location and watch for changes
 */
function getUserLocationForMap() {
    if (!navigator.geolocation) {
        notify.warning('Geolocation tidak didukung');
        return;
    }

    // Get current position dengan timeout lebih panjang
    navigator.geolocation.getCurrentPosition(
        position => updateUserLocation(position),
        error => {
            handleLocationError(error);
            // Retry dengan accuracy lebih rendah
            setTimeout(() => {
                navigator.geolocation.getCurrentPosition(
                    position => updateUserLocation(position),
                    error => console.warn('Retry juga gagal:', error),
                    {
                        enableHighAccuracy: false,
                        timeout: 15000,
                        maximumAge: 0
                    }
                );
            }, 2000);
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );

    // Watch position dengan tolerance lebih tinggi
    watchId = navigator.geolocation.watchPosition(
        position => updateUserLocation(position),
        error => console.warn('Watch error:', error),
        {
            enableHighAccuracy: false, // Ubah ke false
            timeout: 15000, // Lebih panjang
            maximumAge: 5000 // Cache 5 detik
        }
    );
}

/**
 * Update user location on map
 */
function updateUserLocation(position) {
    const lat = position.coords.latitude;
    const lon = position.coords.longitude;
    const accuracy = position.coords.accuracy;

    console.log(`📍 User location: ${lat.toFixed(6)}, ${lon.toFixed(6)} (±${accuracy.toFixed(0)}m)`);

    // Remove old markers
    if (userMarker) map.removeLayer(userMarker);
    if (accuracyCircle) map.removeLayer(accuracyCircle);

    // Create user marker
    userMarker = L.marker([lat, lon], {
        icon: L.icon({
            iconUrl: 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22%3E%3Ccircle cx=%2212%22 cy=%2212%22 r=%228%22 fill=%22%2348bb78%22/%3E%3C/svg%3E',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        }),
        zIndexOffset: 1000
    }).addTo(map);

    // Add accuracy circle
    accuracyCircle = L.circle([lat, lon], {
        radius: accuracy,
        color: '#4299e1',
        fillColor: '#4299e1',
        fillOpacity: 0.1,
        weight: 1,
        dashArray: '5, 5'
    }).addTo(map);

    // Check if inside polygon
    const isInside = checkIfInsidePolygon(lat, lon);
    const timestamp = new Date(position.timestamp).toLocaleTimeString('id-ID');

    // Create popup
    userMarker.bindPopup(`
        <div style="text-align: center; padding: 5px;">
            <strong><i class="fas fa-map-marker-alt" style="color: #48bb78;"></i> Lokasi Anda</strong><br>
            <hr style="margin: 5px 0;">
            <small><strong>Koordinat:</strong></small><br>
            <small>Lat: ${lat.toFixed(6)}</small><br>
            <small>Lon: ${lon.toFixed(6)}</small><br>
            <small class="text-muted">Akurasi: ±${accuracy.toFixed(0)} meter</small><br>
            <small class="text-muted">Update: ${timestamp}</small><br>
            ${isInside ?
                '<span style="color: #48bb78;"><i class="fas fa-check-circle"></i> Dalam area</span>' :
                '<span style="color: #f56565;"><i class="fas fa-times-circle"></i> Di luar area</span>'
            }
        </div>
    `, { maxWidth: 200, autoPan: false }).openPopup();

    // Update status badge
    updateMapStatusBadge(isInside);

    // Center map to user location on first load
    if (!window.mapCenteredToUser) {
        map.setView([lat, lon], 18);
        window.mapCenteredToUser = true;

        setTimeout(() => {
            if (userMarker) {
                userMarker.openPopup();
            }
        }, 500);
    }
}

/**
 * Handle location errors
 */
function handleLocationError(error) {
    let message = 'Tidak dapat mendeteksi lokasi Anda. ';

    switch(error.code) {
        case error.PERMISSION_DENIED:
            message += 'Akses lokasi ditolak. Mohon izinkan akses lokasi.';
            break;
        case error.POSITION_UNAVAILABLE:
            message += 'Informasi lokasi tidak tersedia.';
            break;
        case error.TIMEOUT:
            message += 'Waktu permintaan lokasi habis.';
            break;
        default:
            message += 'Terjadi kesalahan tidak dikenal.';
    }

    console.error('Geolocation error:', error);
    notify.warning(message, 'Info Lokasi');
}

/**
 * Update map status badge
 */
function updateMapStatusBadge(isInside) {
    const statusBadge = document.getElementById('map-status');
    if (!statusBadge) return;

    if (isInside) {
        statusBadge.className = 'map-status-badge in-area';
        statusBadge.innerHTML = '<i class="fas fa-check-circle"></i> Dalam Area Absensi';
    } else {
        statusBadge.className = 'map-status-badge out-area';
        statusBadge.innerHTML = '<i class="fas fa-times-circle"></i> Di Luar Area';
    }
}

// ============================================
// POLYGON POINT-IN-POLYGON CHECK
// ============================================

/**
 * Check if point is inside any polygon
 */
function checkIfInsidePolygon(lat, lon) {
    if (polygonLayers.length === 0) return false;

    const point = L.latLng(lat, lon);

    for (let layer of polygonLayers) {
        const bounds = layer.getBounds();
        if (bounds.contains(point)) {
            const polygon = layer.getLatLngs()[0];
            if (isPointInPolygon(point, polygon)) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Ray casting algorithm for point-in-polygon
 */
function isPointInPolygon(point, polygon) {
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

// ============================================
// CLEANUP
// ============================================

/**
 * Cleanup map resources
 */
function cleanupMap() {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
    }

    if (map) {
        map.remove();
        map = null;
    }
}

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const mapContainer = document.getElementById('map-container');
    if (!mapContainer) {
        console.warn('Map container not found');
        return;
    }

    initializeMap();
    console.log('✅ Absensi map script loaded');
});

// Cleanup on page unload
window.addEventListener('beforeunload', cleanupMap);
