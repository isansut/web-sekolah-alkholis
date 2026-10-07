/**
 * SMA Al-Kholis Website - Modern JavaScript
 * Enhanced functionality and user experience
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // =====================================================
    // NAVIGATION FUNCTIONALITY
    // =====================================================

    // Navbar scroll effect
    const navbar = document.getElementById('mainNavbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // =====================================================
    // HERO SLIDER FUNCTIONALITY
    // =====================================================

    // Hero slider functionality
    const heroSlides = document.querySelectorAll('.hero-slide');
    const sliderIndicators = document.querySelectorAll('.slider-indicator');
    const sliderPrev = document.querySelector('.slider-prev');
    const sliderNext = document.querySelector('.slider-next');
    
    let currentSlide = 0;
    let slideInterval;

    function showSlide(index) {
        // Remove active class from all slides and indicators
        heroSlides.forEach(slide => slide.classList.remove('active'));
        sliderIndicators.forEach(indicator => indicator.classList.remove('active'));
        
        // Add active class to current slide and indicator
        heroSlides[index].classList.add('active');
        sliderIndicators[index].classList.add('active');
        
        currentSlide = index;
    }

    function nextSlide() {
        const nextIndex = (currentSlide + 1) % heroSlides.length;
        showSlide(nextIndex);
    }

    function prevSlide() {
        const prevIndex = (currentSlide - 1 + heroSlides.length) % heroSlides.length;
        showSlide(prevIndex);
    }

    function startSlideShow() {
        slideInterval = setInterval(nextSlide, 5000);
    }

    function stopSlideShow() {
        clearInterval(slideInterval);
    }

    // Initialize slider
    if (heroSlides.length > 1) {
        // Add click event listeners to indicators
        sliderIndicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                showSlide(index);
                stopSlideShow();
                startSlideShow();
            });
        });

        // Add click event listeners to navigation buttons
        if (sliderPrev) {
            sliderPrev.addEventListener('click', () => {
                prevSlide();
                stopSlideShow();
                startSlideShow();
            });
        }

        if (sliderNext) {
            sliderNext.addEventListener('click', () => {
                nextSlide();
                stopSlideShow();
                startSlideShow();
            });
        }

        // Start automatic slideshow
        startSlideShow();

        // Pause slideshow on hover
        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            heroSection.addEventListener('mouseenter', stopSlideShow);
            heroSection.addEventListener('mouseleave', startSlideShow);
        }
    }

    // =====================================================
    // ANIMATION ON SCROLL
    // =====================================================

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.feature-item, .card, .gallery-item').forEach(el => {
        observer.observe(el);
    });

    // =====================================================
    // BACK TO TOP FUNCTIONALITY
    // =====================================================

    const backToTopBtn = document.getElementById('backToTop');
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // =====================================================
    // LIGHTBOX FUNCTIONALITY
    // =====================================================

    // Global lightbox function
    window.openLightbox = function(imageSrc, title) {
        const lightboxModal = document.getElementById('lightboxModal');
        const lightboxImage = document.getElementById('lightboxImage');
        const lightboxTitle = document.getElementById('lightboxTitle');
        
        if (lightboxModal && lightboxImage && lightboxTitle) {
            lightboxImage.src = imageSrc;
            lightboxTitle.textContent = title;
            
            const modal = new bootstrap.Modal(lightboxModal);
            modal.show();
        }
    };

    // =====================================================
    // FORM ENHANCEMENTS
    // =====================================================

    // Form validation and enhancement
    document.querySelectorAll('form').forEach(form => {
        // Add floating label effect
        form.querySelectorAll('.form-control').forEach(input => {
            if (input.value) {
                input.classList.add('has-value');
            }
            
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
                this.parentElement.classList.remove('focused');
            });
            
            input.addEventListener('input', function() {
                if (this.value) {
                    this.classList.add('has-value');
                } else {
                    this.classList.remove('has-value');
                }
            });
        });

        // Form submission enhancement
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
                submitBtn.disabled = true;
                
                // Re-enable button after 3 seconds (fallback)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 3000);
            }
        });
    });

    // =====================================================
    // SEARCH FUNCTIONALITY
    // =====================================================

    // Enhanced search with debouncing
    const searchInputs = document.querySelectorAll('input[name="search"]');
    searchInputs.forEach(input => {
        let searchTimeout;
        
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Add loading state
                this.classList.add('searching');
                
                // Simulate search delay
                setTimeout(() => {
                    this.classList.remove('searching');
                }, 500);
            }, 300);
        });
    });

    // =====================================================
    // CARD INTERACTIONS
    // =====================================================

    // Enhanced card interactions
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // =====================================================
    // IMAGE LAZY LOADING
    // =====================================================

    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // =====================================================
    // ACCESSIBILITY ENHANCEMENTS
    // =====================================================

    // Keyboard navigation for cards
    document.querySelectorAll('.card').forEach(card => {
        card.setAttribute('tabindex', '0');
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const link = this.querySelector('a');
                if (link) {
                    link.click();
                }
            }
        });
    });

    // Focus management for modals
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const firstFocusable = this.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (firstFocusable) {
                firstFocusable.focus();
            }
        });
    });

    // =====================================================
    // PERFORMANCE OPTIMIZATIONS
    // =====================================================

    // Debounce scroll events
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Optimized scroll handler
    const optimizedScrollHandler = debounce(function() {
        // Handle scroll-based animations and effects
        const scrolled = window.scrollY > 50;
        if (navbar) {
            navbar.classList.toggle('scrolled', scrolled);
        }
    }, 10);

    window.addEventListener('scroll', optimizedScrollHandler);

    // =====================================================
    // UTILITY FUNCTIONS
    // =====================================================

    // Format numbers with commas
    window.formatNumber = function(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    };

    // Format dates
    window.formatDate = function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    // Truncate text
    window.truncateText = function(text, length) {
        if (text.length <= length) return text;
        return text.substring(0, length) + '...';
    };

    // =====================================================
    // ERROR HANDLING
    // =====================================================

    // Global error handler
    window.addEventListener('error', function(e) {
        console.error('JavaScript error:', e.error);
        // You can add error reporting logic here
    });

    // Unhandled promise rejection handler
    window.addEventListener('unhandledrejection', function(e) {
        console.error('Unhandled promise rejection:', e.reason);
        // You can add error reporting logic here
    });

    // =====================================================
    // INITIALIZATION
    // =====================================================

    // Initialize all components
    function initializeComponents() {
        console.log('SMA Al-Kholis Website initialized successfully!');
        
        // Add any additional initialization logic here
        document.body.classList.add('js-loaded');
    }

    // Call initialization
    initializeComponents();
});

// =====================================================
// GLOBAL FUNCTIONS
// =====================================================

// Show notification/toast
window.showNotification = function(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
};

// Loading state management
window.setLoadingState = function(element, isLoading) {
    if (isLoading) {
        element.disabled = true;
        element.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
    } else {
        element.disabled = false;
        element.innerHTML = element.dataset.originalText || 'Submit';
    }
}; 