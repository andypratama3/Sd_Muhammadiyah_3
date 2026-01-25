/**
 * Map & Location Management - FIXED VERSION v2
 * File: public/js/absensi-map.js
 *
 * CHANGES:
 * - Added map ready flag
 * - Emit custom event when map fully loaded
 * - Better initialization sequence
 */

// ============================================
// GLOBAL VARIABLES
// ============================================

let map = null;
let userMarker = null;
let accuracyCircle = null;
let polygonLayers = [];
let mapReady = false;

// ============================================
// MAP INITIALIZATION
// ============================================

/**
 * Initialize Leaflet map
 */
function initializeMap() {
    console.log('🗺️ Initializing map...');

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

    // Load KML polygon then mark as ready
    loadKmlPolygon().then(() => {
        mapReady = true;

        // Export globals
        window.map = map;
        window.userMarker = userMarker;
        window.accuracyCircle = accuracyCircle;
        window.polygonLayers = polygonLayers;
        window.mapReady = mapReady;

        console.log('✅ Map fully loaded and ready');

        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('mapReady', {
            detail: { map, polygonLayers }
        }));
    });
}

// ============================================
// KML POLYGON LOADING
// ============================================

/**
 * Load KML polygon from server
 */
async function loadKmlPolygon() {
    try {
        console.log('📥 Loading KML polygons...');

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
// INITIAL USER LOCATION (ONE TIME ONLY)
// ============================================

/**
 * Create initial user marker (called once from realtime tracker)
 */
function createInitialUserMarker(latitude, longitude, accuracy) {
    if (!map) {
        console.warn('⚠️ Map not ready for marker creation');
        return;
    }

    console.log('📍 Creating initial user marker...');

    // Remove old markers if exist
    if (userMarker) map.removeLayer(userMarker);
    if (accuracyCircle) map.removeLayer(accuracyCircle);

    // Create user marker
    userMarker = L.marker([latitude, longitude], {
        icon: L.icon({
            iconUrl: 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22%3E%3Ccircle cx=%2212%22 cy=%2212%22 r=%228%22 fill=%22%2348bb78%22/%3E%3C/svg%3E',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        }),
        zIndexOffset: 1000
    }).addTo(map);

    // Add accuracy circle
    accuracyCircle = L.circle([latitude, longitude], {
        radius: accuracy,
        color: '#4299e1',
        fillColor: '#4299e1',
        fillOpacity: 0.1,
        weight: 1,
        dashArray: '5, 5'
    }).addTo(map);

    // Export to global
    window.userMarker = userMarker;
    window.accuracyCircle = accuracyCircle;

    // Center map to user location
    map.setView([latitude, longitude], 18, {
        animate: true,
        duration: 1
    });

    window.mapCenteredToUser = true;

    console.log('✅ Initial user marker created');
}

// ============================================
// UPDATE MAP STATUS BADGE
// ============================================

/**
 * Update map status badge (called from realtime tracker)
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
    if (map) {
        map.remove();
        map = null;
        mapReady = false;
        window.mapReady = false;
    }
    console.log('🧹 Map cleaned up');
}

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const mapContainer = document.getElementById('map-container');
    if (!mapContainer) {
        console.warn('❌ Map container not found');
        return;
    }

    // Initialize map
    initializeMap();

    console.log('✅ Absensi map script loaded');
});

// Cleanup on page unload
window.addEventListener('beforeunload', cleanupMap);

// Export functions untuk digunakan realtime tracker
window.checkIfInsidePolygon = checkIfInsidePolygon;
window.updateMapStatusBadge = updateMapStatusBadge;
window.createInitialUserMarker = createInitialUserMarker;
