// Contact Page JavaScript Functionality
class ContactPage {
    constructor() {
        this.init();
    }

    init() {
        this.setupFormValidation();
        this.setupWhatsAppValidation();
        this.setupAutoHideAlerts();
        this.setupSmoothScrolling();
        this.setupAnimations();
        this.setupMobileEnhancements();
        this.setupPerformanceOptimization();
    }

    setupFormValidation() {
        const form = document.getElementById('contactForm');
        const submitBtn = document.querySelector('.submit-btn');
        
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        }
        
        // Form submission loading state
        if (form && submitBtn) {
            form.addEventListener('submit', () => {
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Mengirim...';
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
            });
        }
    }

    setupWhatsAppValidation() {
        const whatsappInput = document.getElementById('whatsapp');
        if (whatsappInput) {
            // Real-time formatting
            whatsappInput.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0 && !value.startsWith('08')) {
                    value = '08' + value;
                }
                if (value.length > 13) {
                    value = value.substring(0, 13);
                }
                e.target.value = value;
            });
            
            // Validation on blur
            whatsappInput.addEventListener('blur', (e) => {
                const value = e.target.value;
                if (value && !/^08[0-9]{8,12}$/.test(value)) {
                    e.target.setCustomValidity('Format nomor WhatsApp tidak valid');
                } else {
                    e.target.setCustomValidity('');
                }
            });
        }
    }

    setupAutoHideAlerts() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.classList.add('fade');
                    setTimeout(() => alert.remove(), 150);
                }
            }, 5000);
        });
    }

    setupSmoothScrolling() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    setupAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observe elements for animation
        document.querySelectorAll('.card, .contact-item, .social-btn').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    }

    setupMobileEnhancements() {
        if (window.innerWidth <= 768) {
            // Add touch-friendly interactions
            document.querySelectorAll('.contact-item, .social-btn').forEach(el => {
                el.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.95)';
                });
                
                el.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        }
    }

    setupPerformanceOptimization() {
        // Lazy load images and iframes
        const lazyElements = document.querySelectorAll('img[data-src], iframe[data-src]');
        if (lazyElements.length > 0) {
            const lazyObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const element = entry.target;
                        element.src = element.dataset.src;
                        element.classList.remove('lazy');
                        lazyObserver.unobserve(element);
                    }
                });
            });
            
            lazyElements.forEach(el => lazyObserver.observe(el));
        }
    }

    // Utility methods
    static showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alertContainer');
        if (alertContainer) {
            const div = document.createElement('div');
            div.className = `alert alert-${type} alert-dismissible fade show`;
            div.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(div);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (div.parentNode) {
                    div.classList.add('fade');
                    setTimeout(() => div.remove(), 150);
                }
            }, 5000);
        }
    }

    static validateWhatsApp(number) {
        return /^08[0-9]{8,12}$/.test(number);
    }

    static formatWhatsApp(number) {
        let value = number.replace(/\D/g, '');
        if (value.length > 0 && !value.startsWith('08')) {
            value = '08' + value;
        }
        if (value.length > 13) {
            value = value.substring(0, 13);
        }
        return value;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ContactPage();
});

// Performance monitoring
window.addEventListener('load', () => {
    // Log page load performance
    if ('performance' in window) {
        const perfData = performance.getEntriesByType('navigation')[0];
        console.log('Page load time:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
    }
});

// Error handling
window.addEventListener('error', (e) => {
    console.error('JavaScript error:', e.error);
    // You can send error to analytics service here
});

// Unhandled promise rejection handling
window.addEventListener('unhandledrejection', (e) => {
    console.error('Unhandled promise rejection:', e.reason);
    e.preventDefault();
}); 