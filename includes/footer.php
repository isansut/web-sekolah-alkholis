    </main>

    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 mb-4">
                    <div class="footer-section">
                        <h5 class="footer-title">
                            <i class="fas fa-school me-2"></i>SMA Al-Kholis
                        </h5>
                        <p class="footer-description">
                            Sekolah Islam Terpadu dengan Akreditasi A yang berkomitmen membentuk generasi unggul dengan pendidikan berkualitas, karakter yang baik, dan prestasi yang membanggakan.
                        </p>
                        <div class="social-links">
                            <a href="#" class="social-link" data-tooltip="Facebook" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link" data-tooltip="Instagram" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link" data-tooltip="YouTube" aria-label="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="#" class="social-link" data-tooltip="WhatsApp" aria-label="WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="footer-section">
                        <h5 class="footer-title">
                            <i class="fas fa-map-marker-alt me-2"></i>Informasi Kontak
                        </h5>
                        <ul class="footer-list">
                            <li class="footer-list-item">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <span><?php echo getSetting('contact_address'); ?></span>
                            </li>
                            <li class="footer-list-item">
                                <i class="fas fa-phone me-2"></i>
                                <a href="tel:<?php echo getSetting('contact_phone'); ?>"><?php echo getSetting('contact_phone'); ?></a>
                            </li>
                            <li class="footer-list-item">
                                <i class="fas fa-envelope me-2"></i>
                                <a href="mailto:<?php echo getSetting('contact_email'); ?>"><?php echo getSetting('contact_email'); ?></a>
                            </li>
                            <li class="footer-list-item">
                                <i class="fas fa-clock me-2"></i>
                                <span>Senin - Jumat: 07:00 - 15:00</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="footer-section">
                        <h5 class="footer-title">
                            <i class="fas fa-link me-2"></i>Tautan Cepat
                        </h5>
                        <ul class="footer-list">
                            <li class="footer-list-item">
                                <a href="berita.php" class="footer-link">
                                    <i class="fas fa-chevron-right me-2"></i>Berita Terbaru
                                </a>
                            </li>
                            <li class="footer-list-item">
                                <a href="guru.php" class="footer-link">
                                    <i class="fas fa-chevron-right me-2"></i>Profil Guru
                                </a>
                            </li>
                            <li class="footer-list-item">
                                <a href="galeri.php" class="footer-link">
                                    <i class="fas fa-chevron-right me-2"></i>Galeri Kegiatan
                                </a>
                            </li>
                            <li class="footer-list-item">
                                <a href="karya-siswa.php" class="footer-link">
                                    <i class="fas fa-chevron-right me-2"></i>Karya Siswa
                                </a>
                            </li>
                            <li class="footer-list-item">
                                <a href="jadwal-pelajaran.php" class="footer-link">
                                    <i class="fas fa-chevron-right me-2"></i>Jadwal Pelajaran
                                </a>
                            </li>
                            <li class="footer-list-item">
                                <a href="kontak.php" class="footer-link">
                                    <i class="fas fa-chevron-right me-2"></i>Hubungi Kami
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="footer-copyright mb-0">
                            &copy; <?php echo date('Y'); ?> SMA Al-Kholis. Semua hak dilindungi.
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="footer-credit mb-0">
                            Dibuat dengan <i class="fas fa-heart text-danger"></i> untuk pendidikan Indonesia
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <!-- Page-specific scripts -->
    <script>
        // Additional page-specific functionality can be added here
        console.log('SMA Al-Kholis Website loaded successfully!');
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('mainNavbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html> 