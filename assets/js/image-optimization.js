/**
 * Image Optimization & Error Handling
 * Handles broken images and provides fallbacks
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle image loading errors
    handleImageErrors();
    
    // Add loading states
    addLoadingStates();
    
    // Optimize image loading
    optimizeImageLoading();
});

/**
 * Handle broken images by replacing with fallback content
 */
function handleImageErrors() {
    const images = document.querySelectorAll('img[src*="uploads/"]');
    
    images.forEach(img => {
        // Add error event listener
        img.addEventListener('error', function() {
            handleBrokenImage(this);
        });
        
        // Add load event listener
        img.addEventListener('load', function() {
            this.classList.remove('img-loading');
            this.classList.add('img-loaded');
        });
        
        // Check if image is already broken
        if (img.complete && img.naturalWidth === 0) {
            handleBrokenImage(img);
        }
    });
}

/**
 * Handle broken image by replacing with fallback
 */
function handleBrokenImage(img) {
    const parent = img.parentElement;
    const cardType = getCardType(parent);
    
    // Remove broken image
    img.style.display = 'none';
    
    // Create fallback content
    const fallback = createImageFallback(cardType);
    
    // Insert fallback
    if (parent.classList.contains('card-img-top')) {
        parent.appendChild(fallback);
    } else {
        img.parentNode.insertBefore(fallback, img.nextSibling);
    }
}

/**
 * Get card type for appropriate fallback
 */
function getCardType(element) {
    if (element.classList.contains('berita-card')) return 'berita';
    if (element.classList.contains('karya-card')) return 'karya';
    if (element.classList.contains('guru-card')) return 'guru';
    if (element.classList.contains('siswa-card')) return 'siswa';
    if (element.classList.contains('galeri-item')) return 'galeri';
    return 'default';
}

/**
 * Create image fallback content
 */
function createImageFallback(type) {
    const fallback = document.createElement('div');
    fallback.className = 'img-fallback';
    
    const icons = {
        'berita': '📰',
        'karya': '🎨',
        'guru': '👨‍🏫',
        'siswa': '👨‍🎓',
        'galeri': '🖼️',
        'default': '📷'
    };
    
    const texts = {
        'berita': 'Gambar Berita',
        'karya': 'Gambar Karya',
        'guru': 'Foto Guru',
        'siswa': 'Foto Siswa',
        'galeri': 'Gambar Galeri',
        'default': 'Gambar'
    };
    
    fallback.innerHTML = `
        <div class="fallback-content">
            <div class="fallback-icon">${icons[type] || icons.default}</div>
            <div class="fallback-text">${texts[type] || texts.default}</div>
            <div class="fallback-subtitle">Tidak tersedia</div>
        </div>
    `;
    
    return fallback;
}

/**
 * Add loading states to images
 */
function addLoadingStates() {
    const images = document.querySelectorAll('img[src*="uploads/"]');
    
    images.forEach(img => {
        // Add loading class initially
        img.classList.add('img-loading');
        
        // Remove loading class when image loads
        if (img.complete) {
            img.classList.remove('img-loading');
        }
    });
}

/**
 * Optimize image loading with lazy loading
 */
function optimizeImageLoading() {
    // Check if Intersection Observer is supported
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                }
            });
        });
        
        // Observe images with data-src attribute
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => imageObserver.observe(img));
    }
}

/**
 * Create default images for missing content
 */
function createDefaultImages() {
    // Berita default
    if (!document.querySelector('img[src*="default-news.jpg"]')) {
        createDefaultImage('default-news.jpg', 'Berita Default');
    }
    
    // Karya default
    if (!document.querySelector('img[src*="default-artwork.jpg"]')) {
        createDefaultImage('default-artwork.jpg', 'Karya Default');
    }
    
    // Guru default
    if (!document.querySelector('img[src*="default-avatar.jpg"]')) {
        createDefaultImage('default-avatar.jpg', 'Avatar Default');
    }
    
    // Galeri default
    if (!document.querySelector('img[src*="default-gallery.jpg"]')) {
        createDefaultImage('default-gallery.jpg', 'Galeri Default');
    }
}

/**
 * Create a default image element
 */
function createDefaultImage(filename, alt) {
    const img = document.createElement('img');
    img.src = `assets/images/${filename}`;
    img.alt = alt;
    img.className = 'default-image';
    img.style.display = 'none';
    
    // Add to body temporarily to trigger download
    document.body.appendChild(img);
    
    // Remove after a short delay
    setTimeout(() => {
        if (document.body.contains(img)) {
            document.body.removeChild(img);
        }
    }, 1000);
}

/**
 * Retry loading failed images
 */
function retryFailedImages() {
    const failedImages = document.querySelectorAll('.img-error');
    
    failedImages.forEach(img => {
        const originalSrc = img.dataset.originalSrc || img.src;
        if (originalSrc) {
            // Remove error state
            img.classList.remove('img-error');
            img.classList.add('img-loading');
            
            // Try to reload
            setTimeout(() => {
                img.src = originalSrc + '?retry=' + Date.now();
            }, 2000);
        }
    });
}

/**
 * Add image optimization attributes
 */
function addImageAttributes() {
    const images = document.querySelectorAll('img[src*="uploads/"]');
    
    images.forEach(img => {
        // Add loading attribute for better performance
        if (!img.hasAttribute('loading')) {
            img.setAttribute('loading', 'lazy');
        }
        
        // Add decoding attribute
        if (!img.hasAttribute('decoding')) {
            img.setAttribute('decoding', 'async');
        }
        
        // Store original source for retry
        if (!img.dataset.originalSrc) {
            img.dataset.originalSrc = img.src;
        }
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        handleImageErrors();
        addLoadingStates();
        optimizeImageLoading();
        createDefaultImages();
        addImageAttributes();
    });
} else {
    // DOM is already ready
    handleImageErrors();
    addLoadingStates();
    optimizeImageLoading();
    createDefaultImages();
    addImageAttributes();
}

// Export functions for global use
window.ImageHandler = {
    retryFailedImages,
    createDefaultImages,
    handleImageErrors
}; 