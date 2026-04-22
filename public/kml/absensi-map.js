/**
 * Map & Location Management
 * File: public/js/absensi-map.js
 *
 * Perubahan dari versi sebelumnya:
 * - Polygon area 'kerja'  → warna biru  (#4299e1)
 * - Polygon area 'sholat' → warna hijau (#48bb78)
 * - Backend harus mengembalikan field area_type di setiap polygon
 */

// ============================================
// GLOBAL VARIABLES
// ============================================

let map            = null;
let userMarker     = null;
let accuracyCircle = null;
let polygonLayers  = [];   // semua layer polygon
let mapReady       = false;

// ============================================
// MAP INITIALIZATION
// ============================================

function initializeMap() {
    console.log('🗺️ Initializing map...');

    map = L.map('map-container', {
        center:             [-0.5093246, 117.1298813],
        zoom:               17,
        zoomControl:        true,
        attributionControl: true
    });

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom:     19,
        minZoom:     10,
        crossOrigin: true
    }).addTo(map);

    L.control.scale({ imperial: false, metric: true }).addTo(map);

    setTimeout(() => {
        if (map) {
            map.invalidateSize();
            console.log('🔄 Map size invalidated');
        }
    }, 100);

    loadKmlPolygon().then(() => {
        mapReady = true;

        window.map            = map;
        window.userMarker     = userMarker;
        window.accuracyCircle = accuracyCircle;
        window.polygonLayers  = polygonLayers;
        window.mapReady       = mapReady;

        console.log('✅ Map fully loaded and ready');

        window.dispatchEvent(new CustomEvent('mapReady', {
            detail: { map, polygonLayers }
        }));
    });
}

// ============================================
// STYLE PER AREA TYPE
// ============================================

/**
 * Kembalikan style Leaflet berdasarkan tipe area.
 *
 * kerja  → biru
 * sholat → hijau
 * lainnya → abu-abu
 */
function getPolygonStyle(areaType) {
    const styles = {
        kerja: {
            color:       '#2b6cb0',
            fillColor:   '#4299e1',
            fillOpacity: 0.25,
            weight:      3,
            opacity:     0.9
        },
        sholat: {
            color:       '#276749',
            fillColor:   '#48bb78',
            fillOpacity: 0.25,
            weight:      3,
            opacity:     0.9
        }
    };

    return styles[areaType] ?? {
        color:       '#718096',
        fillColor:   '#a0aec0',
        fillOpacity: 0.2,
        weight:      2,
        opacity:     0.8
    };
}

/**
 * Kembalikan icon/label berdasarkan tipe area untuk popup.
 */
function getAreaLabel(areaType) {
    const labels = {
        kerja:  { icon: 'fa-school',  text: 'Area Kerja'  },
        sholat: { icon: 'fa-mosque',  text: 'Area Sholat' }
    };
    return labels[areaType] ?? { icon: 'fa-map-marker-alt', text: 'Area' };
}

// ============================================
// KML POLYGON LOADING
// ============================================

async function loadKmlPolygon() {
    try {
        console.log('📥 Loading KML polygons...');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch('/kml/data', {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept':       'application/json'
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

        // Hapus polygon lama
        polygonLayers.forEach(layer => map.removeLayer(layer));
        polygonLayers = [];

        polygons.forEach((polygon, index) => {
            const coordinates = polygon.coordinates.map(coord => [coord.lat, coord.lon]);

            if (coordinates.length < 3) {
                console.warn(`Polygon ${index} memiliki koordinat tidak valid`);
                return;
            }

            const areaType  = polygon.area_type ?? 'kerja';
            const style     = getPolygonStyle(areaType);
            const areaLabel = getAreaLabel(areaType);

            const polygonLayer = L.polygon(coordinates, style).addTo(map);

            polygonLayer.bindPopup(`
                <div style="text-align:center; padding:5px; min-width:160px;">
                    <strong>
                        <i class="fas ${areaLabel.icon}"></i>
                        ${escapeHtml(polygon.name)}
                    </strong>
                    <br>
                    <span class="badge" style="background:${style.color}; color:#fff; font-size:11px; padding:2px 8px; border-radius:10px;">
                        ${areaLabel.text}
                    </span>
                    <br>
                    <small class="text-muted">${coordinates.length} titik koordinat</small>
                </div>
            `, { maxWidth: 220 });

            // Simpan area_type di layer untuk keperluan checkIfInsidePolygon
            polygonLayer._areaType = areaType;

            polygonLayers.push(polygonLayer);
            console.log(`✅ Polygon ${index + 1} loaded: [${areaType}] ${polygon.name}`);
        });

        if (polygonLayers.length > 0) {
            const group = L.featureGroup(polygonLayers);
            map.fitBounds(group.getBounds().pad(0.1));
        }

        const kerjaCount  = polygonLayers.filter(l => l._areaType === 'kerja').length;
        const sholatCount = polygonLayers.filter(l => l._areaType === 'sholat').length;
        notify.success(
            `${polygons.length} area dimuat (${kerjaCount} kerja, ${sholatCount} sholat)`,
            'Peta Siap'
        );

    } catch (error) {
        console.error('❌ Error loading KML:', error);
        notify.error('Gagal memuat area absensi: ' + error.message, 'Error Peta');
    }
}

// ============================================
// USER MARKER
// ============================================

function createInitialUserMarker(latitude, longitude, accuracy) {
    if (!map) {
        console.warn('⚠️ Map belum siap');
        return;
    }

    console.log('📍 Creating user marker...');

    if (userMarker)     map.removeLayer(userMarker);
    if (accuracyCircle) map.removeLayer(accuracyCircle);

    userMarker = L.marker([latitude, longitude], {
        icon: L.icon({
            iconUrl: 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22%3E%3Ccircle cx=%2212%22 cy=%2212%22 r=%228%22 fill=%22%2348bb78%22/%3E%3C/svg%3E',
            iconSize:   [24, 24],
            iconAnchor: [12, 12]
        }),
        zIndexOffset: 1000
    }).addTo(map);

    accuracyCircle = L.circle([latitude, longitude], {
        radius:      accuracy,
        color:       '#4299e1',
        fillColor:   '#4299e1',
        fillOpacity: 0.1,
        weight:      1,
        dashArray:   '5, 5'
    }).addTo(map);

    window.userMarker     = userMarker;
    window.accuracyCircle = accuracyCircle;

    map.setView([latitude, longitude], 18, { animate: true, duration: 1 });
    window.mapCenteredToUser = true;

    console.log('✅ User marker created');
}

// ============================================
// MAP STATUS BADGE
// ============================================

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
// POINT-IN-POLYGON CHECK
// ============================================

/**
 * Cek apakah titik berada dalam polygon manapun.
 * Mengembalikan false atau { inside: true, areaType, areaName }.
 */
function checkIfInsidePolygon(lat, lon) {
    if (polygonLayers.length === 0) return false;

    const point = L.latLng(lat, lon);

    for (let layer of polygonLayers) {
        const bounds = layer.getBounds();
        if (bounds.contains(point)) {
            const polygon = layer.getLatLngs()[0];
            if (isPointInPolygon(point, polygon)) {
                return {
                    inside:   true,
                    areaType: layer._areaType ?? 'kerja'
                };
            }
        }
    }

    return false;
}

/**
 * Cek apakah titik berada di dalam polygon bertipe tertentu.
 * Digunakan frontend untuk aktif/nonaktifkan tombol sholat.
 *
 * @param {number} lat
 * @param {number} lon
 * @param {string} type  'kerja' | 'sholat'
 * @returns {boolean}
 */
function checkIfInsidePolygonByType(lat, lon, type) {
    if (polygonLayers.length === 0) return false;

    const point = L.latLng(lat, lon);

    for (let layer of polygonLayers) {
        if ((layer._areaType ?? 'kerja') !== type) continue;

        const bounds = layer.getBounds();
        if (bounds.contains(point)) {
            const polygon = layer.getLatLngs()[0];
            if (isPointInPolygon(point, polygon)) return true;
        }
    }

    return false;
}

/**
 * Ray casting algorithm
 */
function isPointInPolygon(point, polygon) {
    let inside = false;
    const x = point.lat;
    const y = point.lng;

    for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
        const xi = polygon[i].lat, yi = polygon[i].lng;
        const xj = polygon[j].lat, yj = polygon[j].lng;

        const intersect = ((yi > y) !== (yj > y)) &&
            (x < (xj - xi) * (y - yi) / (yj - yi) + xi);

        if (intersect) inside = !inside;
    }

    return inside;
}

// ============================================
// CLEANUP
// ============================================

function cleanupMap() {
    if (map) {
        map.remove();
        map      = null;
        mapReady = false;
        window.mapReady = false;
    }
    console.log('🧹 Map cleaned up');
}

// ============================================
// INIT
// ============================================

document.addEventListener('DOMContentLoaded', function () {
    const mapContainer = document.getElementById('map-container');
    if (!mapContainer) {
        console.warn('❌ Map container not found');
        return;
    }

    initializeMap();
    console.log('✅ absensi-map.js loaded');
});

window.addEventListener('beforeunload', cleanupMap);

// Export untuk digunakan realtime tracker & main
window.checkIfInsidePolygon       = checkIfInsidePolygon;
window.checkIfInsidePolygonByType = checkIfInsidePolygonByType;
window.updateMapStatusBadge       = updateMapStatusBadge;
window.createInitialUserMarker    = createInitialUserMarker;
