/**
 * Navbar Fixes - Ensure proper functionality and appearance
 */

document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.getElementById('mainNavbar');
    
    if (navbar) {
        // Fix navbar height and positioning
        function fixNavbarHeight() {
            const navbarHeight = navbar.offsetHeight;
            const mainContent = document.querySelector('main');
            
            if (mainContent) {
                mainContent.style.marginTop = navbarHeight + 'px';
            }
        }
        
        // Handle navbar scrolling effects
        function handleNavbarScroll() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
        
        // Fix dropdown positioning
        function fixDropdownPositioning() {
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
                
                if (dropdownMenu && dropdownToggle) {
                    // Ensure dropdown is properly positioned
                    dropdownMenu.style.position = 'absolute';
                    dropdownMenu.style.top = '100%';
                    dropdownMenu.style.left = '0';
                    dropdownMenu.style.zIndex = '1000';
                }
            });
        }
        
        // Fix mobile navbar issues
        function fixMobileNavbar() {
            const navbarToggler = document.querySelector('.navbar-toggler');
            const navbarCollapse = document.querySelector('.navbar-collapse');
            
            if (navbarToggler && navbarCollapse) {
                navbarToggler.addEventListener('click', function() {
                    // Ensure proper mobile navbar behavior
                    setTimeout(() => {
                        fixDropdownPositioning();
                    }, 100);
                });
            }
        }
        
        // Fix navbar item spacing
        function fixNavbarSpacing() {
            const navItems = document.querySelectorAll('.navbar-nav .nav-item');
            
            navItems.forEach((item, index) => {
                if (index < navItems.length - 1) {
                    item.style.marginRight = '0.1rem';
                }
            });
        }
        
        // Ensure navbar is visible and properly sized
        function ensureNavbarVisibility() {
            navbar.style.opacity = '1';
            navbar.style.visibility = 'visible';
            navbar.style.overflow = 'visible';
            
            // Force proper height
            navbar.style.minHeight = '60px';
            navbar.style.maxHeight = '70px';
        }
        
        // Initialize all fixes
        function initNavbarFixes() {
            fixNavbarHeight();
            fixDropdownPositioning();
            fixMobileNavbar();
            fixNavbarSpacing();
            ensureNavbarVisibility();
            
            // Add scroll event listener
            window.addEventListener('scroll', handleNavbarScroll);
            
            // Add resize event listener
            window.addEventListener('resize', () => {
                fixNavbarHeight();
                fixDropdownPositioning();
            });
        }
        
        // Run fixes after a short delay to ensure DOM is ready
        setTimeout(initNavbarFixes, 100);
        
        // Run fixes again after images load
        window.addEventListener('load', initNavbarFixes);
    }
});

// Additional fixes for Bootstrap dropdown issues
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('dropdown-toggle')) {
        const dropdown = e.target.closest('.dropdown');
        if (dropdown) {
            // Ensure dropdown menu is properly positioned
            const dropdownMenu = dropdown.querySelector('.dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.style.position = 'absolute';
                dropdownMenu.style.top = '100%';
                dropdownMenu.style.left = '0';
                dropdownMenu.style.zIndex = '1000';
            }
        }
    }
});

// Fix for navbar collapse on mobile
document.addEventListener('DOMContentLoaded', function() {
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const navbarToggler = document.querySelector('.navbar-toggler');
    
    if (navbarCollapse && navbarToggler) {
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navbar.contains(e.target) && navbarCollapse.classList.contains('show')) {
                navbarToggler.click();
            }
        });
        
        // Close mobile menu when clicking on a nav link
        const navLinks = navbarCollapse.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (navbarCollapse.classList.contains('show')) {
                    navbarToggler.click();
                }
            });
        });
    }
});
