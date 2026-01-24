// ============================================
// ABSENSI MAP - SD Muhammadiyah 3 Samarinda
// ============================================

// Global variables
let map = null;
let userMarker = null;
let accuracyCircle = null;
let polygonLayers = [];
let watchId = null;

// ============================================
// MAP INITIALIZATION
// ============================================

function initializeMap() {
    // Initialize map centered on SD Muhammadiyah 3 Samarinda
    map = L.map('map-container', {
        center: [-0.5093246, 117.1298813],
        zoom: 17,
        zoomControl: true,
        attributionControl: true,
        preferCanvas: false
    });

    // Add OpenStreetMap tiles - using direct tile.openstreetmap.org
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

    // FIX: Force map to recalculate size after DOM is ready
    setTimeout(() => {
        if (map) {
            map.invalidateSize();
            console.log('🔄 Map size invalidated');
        }
    }, 100);

    // Additional fix for tiles not loading
    map.on('load', () => {
        console.log('✅ Map loaded successfully');
        setTimeout(() => map.invalidateSize(), 200);
    });

    // Load KML polygon
    loadKmlPolygon();

    // Get and show user location
    getUserLocationForMap();

    console.log('🗺️ Map initialized');
}

// ============================================
// LOAD KML POLYGON
// ============================================

async function loadKmlPolygon() {
    try {
        // Get CSRF token
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
            window.notify?.warning('Area absensi belum dikonfigurasi', 'Info Peta');
            return;
        }

        const polygons = result.polygons || [];

        if (polygons.length === 0) {
            window.notify?.info('Tidak ada polygon yang dikonfigurasi', 'Info Peta');
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

            // Create polygon
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
                    <small class="text-muted">Index: ${index + 1} of ${polygons.length}</small>
                </div>
            `, {
                maxWidth: 200
            });

            polygonLayers.push(polygonLayer);

            console.log(`✅ Polygon ${index + 1} loaded: ${polygon.name}`);
        });

        // Fit map to show all polygons
        if (polygonLayers.length > 0) {
            const group = L.featureGroup(polygonLayers);
            map.fitBounds(group.getBounds().pad(0.1));
        }

        window.notify?.success(`${polygons.length} area berhasil dimuat`, 'Peta Siap');

    } catch (error) {
        console.error('❌ Error loading KML:', error);
        window.notify?.error('Gagal memuat area absensi: ' + error.message, 'Error Peta');
    }
}

// ============================================
// USER LOCATION TRACKING
// ============================================

function getUserLocationForMap() {
    if (!navigator.geolocation) {
        window.notify?.warning('Geolocation tidak didukung browser Anda');
        return;
    }

    // Get current position once
    navigator.geolocation.getCurrentPosition(
        position => updateUserLocation(position),
        error => handleLocationError(error),
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );

    // Watch position for real-time updates
    watchId = navigator.geolocation.watchPosition(
        position => updateUserLocation(position),
        error => console.warn('Watch position error:', error),
        {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 1000
        }
    );
}

function updateUserLocation(position) {
    const lat = position.coords.latitude;
    const lon = position.coords.longitude;
    const accuracy = position.coords.accuracy;

    console.log(`📍 User location: ${lat.toFixed(6)}, ${lon.toFixed(6)} (±${accuracy.toFixed(0)}m)`);

    // Remove old markers
    if (userMarker) map.removeLayer(userMarker);
    if (accuracyCircle) map.removeLayer(accuracyCircle);

    // Create custom icon for user location
    const userIcon = L.divIcon({
        className: 'user-location-marker',
        html: `
            <div style="
                background-color: #48bb78;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                border: 3px solid white;
                box-shadow: 0 0 10px rgba(0,0,0,0.5);
                position: relative;
            ">
                <div style="
                    position: absolute;
                    width: 20px;
                    height: 20px;
                    border-radius: 50%;
                    background-color: #48bb78;
                    opacity: 0.3;
                    animation: pulse 2s infinite;
                "></div>
            </div>
        `,
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });

    // Add user marker
    userMarker = L.marker([lat, lon], {
        icon: userIcon,
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

    // Create detailed popup
    const timestamp = new Date(position.timestamp).toLocaleTimeString('id-ID');

    userMarker.bindPopup(`
        <div style="text-align: center; padding: 5px;">
            <strong><i class="fas fa-map-marker-alt" style="color: #48bb78;"></i> Lokasi Anda</strong><br>
            <hr style="margin: 5px 0;">
            <small><strong>Koordinat:</strong></small><br>
            <small>Lat: ${lat.toFixed(6)}</small><br>
            <small>Lon: ${lon.toFixed(6)}</small><br>
            <small class="text-muted">Akurasi: ±${accuracy.toFixed(0)} meter</small><br>
            <small class="text-muted">Update: ${timestamp}</small><br>
            ${checkIfInsidePolygon(lat, lon) ?
                '<span style="color: #48bb78;"><i class="fas fa-check-circle"></i> Dalam area</span>' :
                '<span style="color: #f56565;"><i class="fas fa-times-circle"></i> Di luar area</span>'
            }
        </div>
    `, {
        maxWidth: 200,
        autoPan: false
    }).openPopup();

    // First time only - center map to user location
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
    window.notify?.warning(message, 'Info Lokasi');
}

// ============================================
// POLYGON CHECK
// ============================================

function checkIfInsidePolygon(lat, lon) {
    // Simple point-in-polygon check using Leaflet
    if (polygonLayers.length === 0) return false;

    const point = L.latLng(lat, lon);

    for (let layer of polygonLayers) {
        const bounds = layer.getBounds();
        if (bounds.contains(point)) {
            // More accurate check using ray casting
            const polygon = layer.getLatLngs()[0];
            if (isPointInPolygon(point, polygon)) {
                return true;
            }
        }
    }

    return false;
}

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
// MAP CONTROLS
// ============================================

function addMapControls() {
    // Add custom control for refresh location
    const RefreshControl = L.Control.extend({
        options: {
            position: 'topright'
        },

        onAdd: function(map) {
            const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
            container.innerHTML = `
                <a href="#" title="Refresh lokasi saya" style="
                    background: white;
                    width: 34px;
                    height: 34px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    text-decoration: none;
                    color: #333;
                ">
                    <i class="fas fa-crosshairs"></i>
                </a>
            `;

            container.onclick = function(e) {
                e.preventDefault();
                if (userMarker) {
                    map.setView(userMarker.getLatLng(), 18);
                    userMarker.openPopup();
                } else {
                    getUserLocationForMap();
                }
            };

            return container;
        }
    });

    map.addControl(new RefreshControl());
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

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
// CLEANUP
// ============================================

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
// INITIALIZE ON DOM READY
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Check if map container exists
    const mapContainer = document.getElementById('map-container');
    if (!mapContainer) {
        console.warn('Map container not found');
        return;
    }

    // Initialize map
    initializeMap();

    // Add custom controls
    addMapControls();

    // Add CSS animation for pulsing marker
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.3;
            }
            50% {
                transform: scale(2);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 0.3;
            }
        }
    `;
    document.head.appendChild(style);

    console.log('✅ Absensi map script loaded');
});

// Cleanup on page unload
window.addEventListener('beforeunload', cleanupMap);
