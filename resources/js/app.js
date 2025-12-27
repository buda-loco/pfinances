import './bootstrap';
import Alpine from 'alpinejs';
import nprogress from 'nprogress';
import 'nprogress/nprogress.css';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

window.Alpine = Alpine;
window.nprogress = nprogress;
window.flatpickr = flatpickr;

// Configure NProgress
nprogress.configure({
    showSpinner: false,
    trickleSpeed: 200,
    minimum: 0.1
});

// SPA-like behavior for page transitions
document.addEventListener('DOMContentLoaded', () => {
    // Show progress bar on link clicks
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (link &&
            link.href &&
            link.href.startsWith(window.location.origin) &&
            !link.hasAttribute('download') &&
            link.target !== '_blank' &&
            !e.ctrlKey && !e.metaKey && !e.shiftKey && !e.altKey) {

            // Start progress bar
            nprogress.start();
        }
    });

    // Handle back/forward buttons
    window.addEventListener('popstate', () => {
        nprogress.start();
    });
});

// Finish progress bar on load
window.addEventListener('load', () => {
    nprogress.done();
});

// Also handle Alpine.js events if needed
document.addEventListener('alpine:init', () => {
    // Confirm Dialog Component
    Alpine.data('confirmDialog', () => ({
        title: 'Confirm Action',
        message: 'Are you sure?',
        confirmText: 'Confirm',
        onConfirm: null,
        modalInstance: null,

        init() {
            this.$nextTick(() => {
                const modalEl = document.getElementById('confirm-dialog');
                if (modalEl) {
                    this.modalInstance = new bootstrap.Modal(modalEl, {
                        keyboard: true,
                        backdrop: true
                    });
                }
            });
        },

        open(detail) {
            this.title = detail.title || 'Confirm Action';
            this.message = detail.message || 'Are you sure?';
            this.confirmText = detail.confirmText || 'Confirm';
            this.onConfirm = detail.onConfirm || null;
            if (this.modalInstance) {
                this.modalInstance.show();
            }
        },

        close() {
            if (this.modalInstance) {
                this.modalInstance.hide();
            }
        },

        confirm() {
            if (this.onConfirm) {
                this.onConfirm();
            }
            this.close();
        }
    }));

    // Toast Manager Component
    Alpine.data('toastManager', () => ({
        toasts: [],

        add(message, type = 'info', duration = 5000) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message, type });

            if (duration > 0) {
                setTimeout(() => this.remove(id), duration);
            }
        },

        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }));

    // Command Bar
    const commandBar = document.querySelector('#command-bar');
    if (commandBar) {
        const commandBarModal = new bootstrap.Modal(commandBar);
        const commandBarInput = commandBar.querySelector('input');

        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                commandBarModal.show();
                commandBarInput.focus();
            }
        });
    }
});

// Global toast helper function
window.toast = (message, type = 'info', duration = 5000) => {
    window.dispatchEvent(new CustomEvent('show-toast', {
        detail: { message, type, duration }
    }));
};

Alpine.start();
