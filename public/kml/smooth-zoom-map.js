/**
 * Smooth Zoom Map System
 * File: public/js/smooth-zoom-map.js
 *
 * Features:
 * - Smooth zoom animations
 * - Custom zoom controls
 * - Double-click zoom
 * - Mouse wheel smooth zoom
 * - Location-based auto zoom
 * - Animated focus to markers
 */

class SmoothZoomController {
    constructor(map) {
        this.map = map;
        this.defaultZoom = 17;
        this.minZoom = 10;
        this.maxZoom = 20;
        this.zoomDuration = 800; // milliseconds
        this.isZooming = false;

        // Disable default zoom animation untuk custom control
        this.map.options.zoomAnimation = true;
        this.map.options.zoomAnimationThreshold = 4;

        this.initialize();
    }

    /**
     * Initialize zoom controller
     */
    initialize() {
        this.addCustomZoomControls();
        this.setupMouseWheelZoom();
        this.setupDoubleClickZoom();
        this.setupKeyboardZoom();
        console.log('✅ Smooth zoom controller initialized');
    }

    /**
     * Add custom zoom control buttons
     */
    addCustomZoomControls() {
        const ZoomControl = L.Control.extend({
            options: {
                position: 'topleft'
            },

            onAdd: (map) => {
                const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control custom-zoom-control');
                container.style.cssText = `
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                    overflow: hidden;
                    display: flex;
                    flex-direction: column;
                `;

                // Zoom In Button
                const zoomInBtn = L.DomUtil.create('button', '', container);
                zoomInBtn.innerHTML = '<i class="fas fa-plus"></i>';
                zoomInBtn.style.cssText = `
                    width: 40px;
                    height: 40px;
                    border: none;
                    background: white;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #2d3748;
                    font-size: 18px;
                    transition: all 0.3s ease;
                    border-bottom: 1px solid #e2e8f0;
                `;

                zoomInBtn.onmouseenter = () => {
                    zoomInBtn.style.background = '#f7fafc';
                    zoomInBtn.style.color = '#667eea';
                };
                zoomInBtn.onmouseleave = () => {
                    zoomInBtn.style.background = 'white';
                    zoomInBtn.style.color = '#2d3748';
                };

                L.DomEvent.on(zoomInBtn, 'click', (e) => {
                    L.DomEvent.stopPropagation(e);
                    this.smoothZoomIn();
                });

                // Zoom Out Button
                const zoomOutBtn = L.DomUtil.create('button', '', container);
                zoomOutBtn.innerHTML = '<i class="fas fa-minus"></i>';
                zoomOutBtn.style.cssText = `
                    width: 40px;
                    height: 40px;
                    border: none;
                    background: white;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #2d3748;
                    font-size: 18px;
                    transition: all 0.3s ease;
                `;

                zoomOutBtn.onmouseenter = () => {
                    zoomOutBtn.style.background = '#f7fafc';
                    zoomOutBtn.style.color = '#f56565';
                };
                zoomOutBtn.onmouseleave = () => {
                    zoomOutBtn.style.background = 'white';
                    zoomOutBtn.style.color = '#2d3748';
                };

                L.DomEvent.on(zoomOutBtn, 'click', (e) => {
                    L.DomEvent.stopPropagation(e);
                    this.smoothZoomOut();
                });

                // Fit Bounds Button
                const fitBtn = L.DomUtil.create('button', '', container);
                fitBtn.innerHTML = '<i class="fas fa-expand"></i>';
                fitBtn.title = 'Fit all areas';
                fitBtn.style.cssText = `
                    width: 40px;
                    height: 40px;
                    border: none;
                    background: white;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #2d3748;
                    font-size: 18px;
                    transition: all 0.3s ease;
                    border-top: 1px solid #e2e8f0;
                `;

                fitBtn.onmouseenter = () => {
                    fitBtn.style.background = '#f7fafc';
                    fitBtn.style.color = '#48bb78';
                };
                fitBtn.onmouseleave = () => {
                    fitBtn.style.background = 'white';
                    fitBtn.style.color = '#2d3748';
                };

                L.DomEvent.on(fitBtn, 'click', (e) => {
                    L.DomEvent.stopPropagation(e);
                    this.fitAllLayers();
                });

                return container;
            }
        });

        this.map.addControl(new ZoomControl());
    }

    /**
     * Smooth zoom in dengan animasi
     */
    smoothZoomIn() {
        if (this.isZooming) return;

        const currentZoom = this.map.getZoom();
        const targetZoom = Math.min(currentZoom + 1, this.maxZoom);

        if (currentZoom >= this.maxZoom) {
            console.log('⚠️ Sudah di zoom maksimal');
            return;
        }

        this.smoothZoom(targetZoom);
    }

    /**
     * Smooth zoom out dengan animasi
     */
    smoothZoomOut() {
        if (this.isZooming) return;

        const currentZoom = this.map.getZoom();
        const targetZoom = Math.max(currentZoom - 1, this.minZoom);

        if (currentZoom <= this.minZoom) {
            console.log('⚠️ Sudah di zoom minimal');
            return;
        }

        this.smoothZoom(targetZoom);
    }

    /**
     * Smooth zoom ke level tertentu
     */
    smoothZoom(targetZoom, options = {}) {
        const currentZoom = this.map.getZoom();

        if (currentZoom === targetZoom) return;

        this.isZooming = true;
        const startZoom = currentZoom;
        const startTime = Date.now();
        const duration = options.duration || this.zoomDuration;

        // Use easing function untuk smooth animation
        const easeInOutCubic = (t) => {
            return t < 0.5
                ? 4 * t * t * t
                : 1 - Math.pow(-2 * t + 2, 3) / 2;
        };

        const animateZoom = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeProgress = easeInOutCubic(progress);

            // Interpolate zoom level
            const currentZoomLevel = startZoom + (targetZoom - startZoom) * easeProgress;
            this.map.setZoom(currentZoomLevel, {
                animate: false,
                noMoveStart: true
            });

            if (progress < 1) {
                requestAnimationFrame(animateZoom);
            } else {
                this.map.setZoom(targetZoom, { animate: false });
                this.isZooming = false;
                console.log(`✅ Zoomed to level ${targetZoom}`);
            }
        };

        animateZoom();
    }

    /**
     * Smooth zoom dan pan ke location
     */
    smoothZoomToLocation(latlng, targetZoom = 18, options = {}) {
        if (this.isZooming) return;

        const duration = options.duration || 1000;
        const startZoom = this.map.getZoom();
        const startCenter = this.map.getCenter();
        const startTime = Date.now();

        const easeInOutCubic = (t) => {
            return t < 0.5
                ? 4 * t * t * t
                : 1 - Math.pow(-2 * t + 2, 3) / 2;
        };

        this.isZooming = true;

        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeProgress = easeInOutCubic(progress);

            // Interpolate zoom
            const currentZoom = startZoom + (targetZoom - startZoom) * easeProgress;

            // Interpolate center point
            const lat = startCenter.lat + (latlng.lat - startCenter.lat) * easeProgress;
            const lng = startCenter.lng + (latlng.lng - startCenter.lng) * easeProgress;

            this.map.setView([lat, lng], currentZoom, {
                animate: false,
                noMoveStart: true
            });

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                this.map.setView(latlng, targetZoom, { animate: false });
                this.isZooming = false;
                console.log(`✅ Smooth zoomed to location: ${targetZoom}`);
            }
        };

        animate();
    }

    /**
     * Setup mouse wheel smooth zoom
     */
    setupMouseWheelZoom() {
        this.map.on('wheel', (e) => {
            if (!e.originalEvent.ctrlKey) return;

            e.originalEvent.preventDefault();

            const currentZoom = this.map.getZoom();
            const direction = e.originalEvent.deltaY > 0 ? -1 : 1;
            const targetZoom = Math.max(
                this.minZoom,
                Math.min(currentZoom + direction, this.maxZoom)
            );

            if (currentZoom !== targetZoom) {
                this.smoothZoom(targetZoom, { duration: 500 });
            }
        });

        console.log('✅ Mouse wheel zoom initialized (Ctrl + Scroll)');
    }

    /**
     * Setup double click zoom
     */
    setupDoubleClickZoom() {
        this.map.on('dblclick', (e) => {
            const currentZoom = this.map.getZoom();
            const targetZoom = Math.min(currentZoom + 2, this.maxZoom);

            this.smoothZoomToLocation(e.latlng, targetZoom, {
                duration: 600
            });
        });

        console.log('✅ Double-click zoom initialized');
    }

    /**
     * Setup keyboard zoom controls
     */
    setupKeyboardZoom() {
        document.addEventListener('keydown', (e) => {
            if (!this.map.getContainer().contains(document.activeElement)) return;

            switch(e.key) {
                case '+':
                case '=':
                    e.preventDefault();
                    this.smoothZoomIn();
                    break;
                case '-':
                case '_':
                    e.preventDefault();
                    this.smoothZoomOut();
                    break;
            }
        });

        console.log('✅ Keyboard zoom initialized (+ / -)');
    }

    /**
     * Fit all polygon layers ke viewport
     */
    fitAllLayers() {
        if (!window.polygonLayers || window.polygonLayers.length === 0) {
            console.warn('⚠️ Tidak ada polygon layers');
            return;
        }

        const group = L.featureGroup(window.polygonLayers);
        const bounds = group.getBounds();

        // Smooth animation ke bounds
        this.map.fitBounds(bounds.pad(0.1), {
            animate: true,
            duration: this.zoomDuration,
            easeLinearity: 0.3
        });

        console.log('✅ Fitted all layers');
    }

    /**
     * Focus ke user location dengan zoom
     */
    focusToUserLocation() {
        if (!window.userMarker) return;

        const latlng = window.userMarker.getLatLng();
        this.smoothZoomToLocation(latlng, 18, {
            duration: 800
        });

        console.log('📍 Focused to user location');
    }

    /**
     * Get current zoom level
     */
    getCurrentZoom() {
        return this.map.getZoom();
    }

    /**
     * Set zoom level dengan smooth animation
     */
    setZoom(level, duration = this.zoomDuration) {
        if (level < this.minZoom || level > this.maxZoom) {
            console.warn(`⚠️ Zoom level ${level} diluar range ${this.minZoom}-${this.maxZoom}`);
            return;
        }

        this.smoothZoom(level, { duration });
    }

    /**
     * Get zoom statistics
     */
    getZoomStats() {
        return {
            currentZoom: this.map.getZoom(),
            minZoom: this.minZoom,
            maxZoom: this.maxZoom,
            isZooming: this.isZooming
        };
    }
}

/**
 * Initialize smooth zoom saat map ready
 */
function initializeSmoothZoom() {
    if (!window.map) {
        console.warn('⚠️ Map belum diinisialisasi');
        return;
    }

    window.zoomController = new SmoothZoomController(window.map);
    console.log('✅ Smooth zoom system initialized');

    // Expose methods globally
    window.smoothZoomIn = () => window.zoomController.smoothZoomIn();
    window.smoothZoomOut = () => window.zoomController.smoothZoomOut();
    window.focusToUserLocation = () => window.zoomController.focusToUserLocation();
    window.fitAllLayers = () => window.zoomController.fitAllLayers();
}

/**
 * Auto-initialize when map is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    // Wait untuk map initialization
    const checkMapReady = setInterval(() => {
        if (window.map) {
            clearInterval(checkMapReady);
            setTimeout(() => {
                initializeSmoothZoom();
            }, 500);
        }
    }, 100);
});

// Export untuk digunakan
window.SmoothZoomController = SmoothZoomController;
window.initializeSmoothZoom = initializeSmoothZoom;
