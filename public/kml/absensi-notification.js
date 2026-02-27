/**
 * Toast Notification System
 * File: public/js/absensi-notification.js
 */

class ToastNotification {
    constructor() {
        this.container = document.getElementById('notification-container');
    }

    /**
     * Show notification toast
     */
    show(message, type = 'info', duration = 5000, title = '') {
        const id = Date.now();
        const iconMap = {
            success: 'fas fa-check-circle',
            error:   'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info:    'fas fa-info-circle'
        };

        const titleMap = {
            success: 'Berhasil',
            error:   'Gagal',
            warning: 'Peringatan',
            info:    'Informasi'
        };

        const toast = document.createElement('div');
        toast.className = `notification-toast ${type}`;
        toast.id = `toast-${id}`;
        toast.innerHTML = `
            <div class="notification-content">
                <div class="notification-icon">
                    <i class="${iconMap[type]}"></i>
                </div>
                <div class="notification-text">
                    <p class="notification-title">${title || titleMap[type]}</p>
                    <p class="notification-message">${message}</p>
                </div>
                <button class="notification-close" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            ${duration > 0 ? `<div class="notification-progress" style="--duration: ${duration}ms"></div>` : ''}
        `;

        this.container.appendChild(toast);

        // Close button handler
        toast.querySelector('.notification-close').addEventListener('click', () => {
            this.remove(id);
        });

        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => this.remove(id), duration);
        }

        return id;
    }

    /**
     * Remove notification
     */
    remove(id) {
        const toast = document.getElementById(`toast-${id}`);
        if (toast) {
            toast.classList.add('removing');
            setTimeout(() => {
                if (toast && toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }
    }

    success(message, title = '') { return this.show(message, 'success', 5000, title); }
    error(message, title = '')   { return this.show(message, 'error',   6000, title); }
    warning(message, title = '') { return this.show(message, 'warning', 5000, title); }
    info(message, title = '')    { return this.show(message, 'info',    4000, title); }
}

// Initialize global notify instance
const notify = new ToastNotification();

// ============================================
// LOADING OVERLAY
// ============================================

function showLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.classList.remove('d-none');
}

function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.classList.add('d-none');
}

// ============================================
// UTILITY
// ============================================

/**
 * Escape HTML untuk prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}
