/**
 * Focus to User Location Button System
 * File: public/js/focus-location-button.js
 *
 * Features:
 * - Show/hide focus button based on map movement
 * - Smooth animation to user location
 * - Pulse effect when user location updated
 * - Keyboard shortcut (L key)
 * - Mobile-friendly positioning
 */

class FocusLocationButton {
    constructor(options = {}) {
        this.map = options.map || window.map;
        this.userMarker = null;
        this.isVisible = false;
        this.userHasMoved = false;
        this.mapOriginalView = null;
        this.debounceTimer = null;
        this.debounceDelay = options.debounceDelay || 500;
        this.smoothDuration = options.smoothDuration || 800;

        this.initialize();
    }

    /**
     * Initialize focus button
     */
    initialize() {
        if (!this.map) {
            console.warn('⚠️ Map not available for FocusLocationButton');
            return;
        }

        // Store initial map view
        this.mapOriginalView = {
            center: this.map.getCenter(),
            zoom: this.map.getZoom()
        };

        // Create button element
        this.createButton();

        // Setup event listeners
        this.setupMapListeners();
        this.setupKeyboardShortcuts();

        console.log('✅ FocusLocationButton initialized');
    }

    /**
     * Create focus location button
     */
    createButton() {
        // Create button container
        const buttonContainer = document.createElement('div');
        buttonContainer.id = 'focus-location-container';
        buttonContainer.className = 'focus-location-container';
        buttonContainer.style.cssText = `
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 300;
            pointer-events: none;
        `;

        // Create button
        this.button = document.createElement('button');
        this.button.id = 'btn-focus-location';
        this.button.className = 'focus-location-button';
        this.button.innerHTML = '<i class="fas fa-crosshairs"></i>';
        this.button.title = 'Fokus ke lokasi saya (L)';
        this.button.style.cssText = `
            width: 48px;
            height: 48px;
            border: none;
            border-radius: 50%;
            background: white;
            color: #667eea;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: all;
            z-index: 400;
        `;

        // Add hover effect
        this.button.addEventListener('mouseenter', () => {
            this.button.style.transform = 'scale(1.1)';
            this.button.style.background = '#f7fafc';
            this.button.style.boxShadow = '0 4px 12px rgba(102, 126, 234, 0.3)';
        });

        this.button.addEventListener('mouseleave', () => {
            this.button.style.transform = 'scale(1)';
            this.button.style.background = 'white';
            this.button.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.15)';
        });

        // Add click event
        this.button.addEventListener('click', () => this.focusToUser());

        // Add active effect
        this.button.addEventListener('mousedown', () => {
            this.button.style.transform = 'scale(0.95)';
        });

        this.button.addEventListener('mouseup', () => {
            this.button.style.transform = 'scale(1)';
        });

        // Append to container
        buttonContainer.appendChild(this.button);

        // Append to map container
        const mapContainer = document.getElementById('map-container');
        if (mapContainer && mapContainer.parentNode) {
            mapContainer.parentNode.style.position = 'relative';
            mapContainer.parentNode.appendChild(buttonContainer);
        }

        // Add CSS styles
        this.addStyles();

        console.log('✅ Focus button created');
    }

    /**
     * Add CSS styles untuk focus button
     */
    addStyles() {
        if (document.getElementById('focus-location-styles')) return;

        const style = document.createElement('style');
        style.id = 'focus-location-styles';
        style.textContent = `
            /* Focus Location Button */
            .focus-location-button {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                outline: none;
            }

            .focus-location-button:focus {
                outline: 2px solid #667eea;
                outline-offset: 2px;
            }

            .focus-location-button.active {
                animation: pulseLocation 1.5s ease-in-out infinite;
            }

            @keyframes pulseLocation {
                0%, 100% {
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15),
                                0 0 0 0 rgba(102, 126, 234, 0.4);
                }
                50% {
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15),
                                0 0 0 10px rgba(102, 126, 234, 0);
                }
            }

            /* Fade in/out animation */
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes slideOutDown {
                from {
                    opacity: 1;
                    transform: translateY(0);
                }
                to {
                    opacity: 0;
                    transform: translateY(20px);
                }
            }

            .focus-location-button.show {
                display: flex !important;
                animation: slideInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .focus-location-button.hide {
                animation: slideOutDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                display: none !important;
            }

            /* Dark mode support */
            @media (prefers-color-scheme: dark) {
                .focus-location-button {
                    background: #2d3748;
                    color: #a0aec0;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
                }

                .focus-location-button:hover {
                    background: #4a5568;
                }
            }

            /* Mobile styles */
            @media (max-width: 768px) {
                .focus-location-button {
                    width: 44px;
                    height: 44px;
                    bottom: 15px;
                    right: 15px;
                    font-size: 18px;
                }
            }

            @media (max-width: 576px) {
                .focus-location-button {
                    width: 40px;
                    height: 40px;
                    font-size: 16px;
                }
            }

            /* Accessibility */
            @media (prefers-reduced-motion: reduce) {
                .focus-location-button,
                .focus-location-button.active {
                    transition: none;
                    animation: none;
                }

                @keyframes pulseLocation {
                    0%, 100% {
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                    }
                }
            }

            /* High contrast mode */
            @media (prefers-contrast: more) {
                .focus-location-button {
                    border: 2px solid #333;
                }

                .focus-location-button:focus {
                    outline-width: 3px;
                }
            }
        `;

        document.head.appendChild(style);
    }

    /**
     * Setup map event listeners
     */
    setupMapListeners() {
        // Listen to map drag
        this.map.on('dragstart', () => {
            this.onMapMove();
        });

        // Listen to map zoom
        this.map.on('zoom', () => {
            this.onMapMove();
        });

        // Listen to bounds change dengan debounce
        this.map.on('moveend', () => {
            this.checkMapMoved();
        });
    }

    /**
     * Handle map movement
     */
    onMapMove() {
        // Debounce untuk avoid flickering
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }

        this.debounceTimer = setTimeout(() => {
            this.checkMapMoved();
        }, this.debounceDelay);
    }

    /**
     * Check apakah map sudah moved dari user location
     */
    checkMapMoved() {
        if (!window.userMarker) {
            console.log('⚠️ User marker not available yet');
            return;
        }

        const userLocation = window.userMarker.getLatLng();
        const mapCenter = this.map.getCenter();

        // Calculate distance antara user dan map center
        const distance = mapCenter.distanceTo(userLocation);

        // Show button jika user lebih dari 50 meter dari map center
        if (distance > 50) {
            this.show();
            console.log(`📍 Map moved ${distance.toFixed(0)}m from user location`);
        } else {
            this.hide();
        }
    }

    /**
     * Show focus button
     */
    show() {
        if (this.isVisible) return;

        this.isVisible = true;
        this.button.classList.remove('hide');
        this.button.classList.add('show');
        this.button.style.display = 'flex';

        console.log('👁️ Focus button shown');
    }

    /**
     * Hide focus button
     */
    hide() {
        if (!this.isVisible) return;

        this.isVisible = false;
        this.button.classList.remove('show');
        this.button.classList.add('hide');

        // Delay untuk allow animation
        setTimeout(() => {
            if (!this.isVisible) {
                this.button.style.display = 'none';
            }
        }, 300);

        console.log('👁️ Focus button hidden');
    }

    /**
     * Focus ke user location dengan smooth animation
     */
    focusToUser() {
        if (!window.userMarker) {
            console.warn('⚠️ User marker not available');
            return;
        }

        const userLocation = window.userMarker.getLatLng();

        // Use zoom controller jika available
        if (window.zoomController) {
            window.zoomController.smoothZoomToLocation(userLocation, 18, {
                duration: this.smoothDuration
            });
        } else {
            // Fallback: simple setView
            this.map.setView(userLocation, 18, {
                animate: true,
                duration: this.smoothDuration
            });
        }

        // Open user marker popup
        setTimeout(() => {
            if (window.userMarker) {
                window.userMarker.openPopup();
            }
        }, 300);

        // Trigger success notification
        if (window.notify) {
            window.notify.info('Terfokus ke lokasi Anda', 'Lokasi');
        }

        console.log('📍 Focused to user location');

        // Hide button setelah fokus
        setTimeout(() => {
            this.hide();
        }, 1000);
    }

    /**
     * Show pulse animation
     */
    showPulse() {
        this.button.classList.add('active');
    }

    /**
     * Hide pulse animation
     */
    hidePulse() {
        this.button.classList.remove('active');
    }

    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // L key untuk focus ke user location
            if (e.key.toLowerCase() === 'l' && e.ctrlKey === false && e.altKey === false) {
                // Check jika tidak fokus di input
                if (document.activeElement.tagName !== 'INPUT' &&
                    document.activeElement.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    this.focusToUser();
                }
            }

            // ? key untuk show help
            if (e.key === '?') {
                this.showHelp();
            }
        });

        console.log('✅ Keyboard shortcuts initialized (L = Focus, ? = Help)');
    }

    /**
     * Show help dialog
     */
    showHelp() {
        const helpText = `
Keyboard Shortcuts:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
L    = Fokus ke lokasi saya
+    = Zoom in
-    = Zoom out
?    = Tampilkan bantuan ini
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Mouse:
Double-click = Zoom in 2x ke lokasi
Ctrl+Scroll = Smooth zoom
        `;

        if (window.notify) {
            window.notify.info(helpText, 'Bantuan Shortcut');
        } else {
            alert(helpText);
        }
    }

    /**
     * Update user marker reference
     */
    setUserMarker(marker) {
        this.userMarker = marker;
    }

    /**
     * Get button element
     */
    getButton() {
        return this.button;
    }

    /**
     * Check if button visible
     */
    isButtonVisible() {
        return this.isVisible;
    }
}

/**
 * Initialize focus location button
 */
function initializeFocusLocationButton(options = {}) {
    // Wait untuk map ready
    const waitForMap = setInterval(() => {
        if (window.map && window.mapReady) {
            clearInterval(waitForMap);

            window.focusLocationController = new FocusLocationButton({
                map: window.map,
                debounceDelay: options.debounceDelay || 500,
                smoothDuration: options.smoothDuration || 800
            });

            console.log('✅ Focus location button initialized');
        }
    }, 100);
}

/**
 * Auto-initialize on DOM ready
 */
document.addEventListener('DOMContentLoaded', () => {
    // Check jika sudah ada fokus button
    const existingButton = document.getElementById('btn-focus-location');
    if (!existingButton) {
        setTimeout(() => {
            initializeFocusLocationButton();
        }, 1000);
    }
});

// Export untuk digunakan
window.FocusLocationButton = FocusLocationButton;
window.initializeFocusLocationButton = initializeFocusLocationButton;
