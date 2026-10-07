<?php
$pageTitle = 'Beranda';
require_once 'includes/header.php';

// Get recent data
try {
    $pdo = getDBConnection();
    
    // Test database connection
    $pdo->query("SELECT 1");
    
    // Get recent berita
    $stmt = $pdo->prepare("SELECT * FROM berita WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $recentBerita = $stmt->fetchAll();
    
    // Get recent karya siswa
    $stmt = $pdo->prepare("SELECT * FROM karya_siswa WHERE status = 'approved' ORDER BY created_at DESC LIMIT 6");
    $stmt->execute();
    $recentKarya = $stmt->fetchAll();
    
    // Get active guru
    $stmt = $pdo->prepare("SELECT * FROM guru WHERE status = 'active' ORDER BY nama LIMIT 6");
    $stmt->execute();
    $activeGuru = $stmt->fetchAll();
    
    // Get recent galeri
    $stmt = $pdo->prepare("SELECT * FROM galeri WHERE status = 'active' ORDER BY created_at DESC LIMIT 8");
    $stmt->execute();
    $recentGaleri = $stmt->fetchAll();
    
    // Get pengumuman
    $stmt = $pdo->prepare("SELECT * FROM pengumuman WHERE status = 'published' ORDER BY priority DESC, created_at DESC LIMIT 3");
    $stmt->execute();
    $pengumuman = $stmt->fetchAll();
    
    // Validate data arrays
    $recentBerita = is_array($recentBerita) ? $recentBerita : [];
    $recentKarya = is_array($recentKarya) ? $recentKarya : [];
    $activeGuru = is_array($activeGuru) ? $activeGuru : [];
    $recentGaleri = is_array($recentGaleri) ? $recentGaleri : [];
    $pengumuman = is_array($pengumuman) ? $pengumuman : [];
    
} catch (Exception $e) {
    error_log("Index page error: " . $e->getMessage());
    $recentBerita = [];
    $recentKarya = [];
    $activeGuru = [];
    $recentGaleri = [];
    $pengumuman = [];
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <!-- Background Slider -->
    <div class="hero-slider">
        <div class="hero-slide active" style="background-image: url('assets/images/oke.jpg');"></div>
    </div>
    
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8 hero-content">
                <h1 class="hero-title">Selamat Datang di SMA<br>Islam Terpadu</h1>
                <p class="hero-subtitle">
                    Membentuk generasi unggul dengan pendidikan berkualitas, karakter yang baik, dan prestasi yang membanggakan.
                </p>
                <div class="school-info">
                    <div class="info-badge">
                        <i class="fas fa-mosque"></i>
                        Sekolah Islam Terpadu
                    </div>
                    <div class="info-badge">
                        <i class="fas fa-star"></i>
                        Akreditasi A
                    </div>
                    <div class="info-badge">
                        <i class="fas fa-award"></i>
                        Berprestasi Nasional
                    </div>
                </div>
                <div class="hero-buttons">
                    <a href="berita.php" class="btn btn-light btn-lg">
                        <i class="fas fa-newspaper me-2"></i>Berita Terbaru
                    </a>
                    <a href="kontak.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-envelope me-2"></i>Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Slider Controls -->
    <div class="slider-controls">
        <div class="slider-indicator active" data-slide="0"></div>
        <div class="slider-indicator" data-slide="1"></div>
        <div class="slider-indicator" data-slide="2"></div>
    </div>
    
    <!-- Slider Navigation Buttons -->
    <button class="slider-nav slider-prev" type="button">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="slider-nav slider-next" type="button">
        <i class="fas fa-chevron-right"></i>
    </button>
</section>

<!-- Features Section -->
<section class="section bg-light py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title display-5 fw-bold text-primary mb-3">Keunggulan Kami</h2>
            <p class="section-subtitle lead text-muted">Mengapa memilih SMA Al-Kholis untuk pendidikan putra-putri Anda</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="feature-item text-center p-4 h-100 bg-white rounded-3 shadow-sm border-0">
                    <div class="feature-icon mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 70px; height: 70px;">
                            <i class="fas fa-mosque fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="feature-title h5 fw-bold mb-3">Pendidikan Islam Terpadu</h4>
                    <p class="text-muted mb-0">Mengintegrasikan nilai-nilai Islam dalam setiap aspek pembelajaran dan pengembangan karakter.</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-item text-center p-4 h-100 bg-white rounded-3 shadow-sm border-0">
                    <div class="feature-icon mb-3">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 70px; height: 70px;">
                            <i class="fas fa-graduation-cap fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="feature-title h5 fw-bold mb-3">Akreditasi A</h4>
                    <p class="text-muted mb-0">Memiliki akreditasi A yang menjamin kualitas pendidikan dan pengajaran yang tinggi.</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-item text-center p-4 h-100 bg-white rounded-3 shadow-sm border-0">
                    <div class="feature-icon mb-3">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 70px; height: 70px;">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="feature-title h5 fw-bold mb-3">Guru Berpengalaman</h4>
                    <p class="text-muted mb-0">Didukung oleh tenaga pengajar yang berkualitas dan berpengalaman di bidangnya.</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-item text-center p-4 h-100 bg-white rounded-3 shadow-sm border-0">
                    <div class="feature-icon mb-3">
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 70px; height: 70px;">
                            <i class="fas fa-trophy fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="feature-title h5 fw-bold mb-3">Prestasi Unggulan</h4>
                    <p class="text-muted mb-0">Konsisten meraih prestasi di berbagai bidang akademik dan non-akademik.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Berita Section -->
<?php if (!empty($recentBerita)): ?>
<section class="section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title display-5 fw-bold text-primary mb-3">Berita Terbaru</h2>
            <p class="section-subtitle lead text-muted">Informasi terkini seputar kegiatan dan prestasi SMA Al-Kholis</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($recentBerita as $berita): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card berita-card h-100 border-0 shadow-sm hover-lift">
                    <div class="position-relative">
                        <img src="<?php echo !empty($berita['image']) ? $berita['image'] : 'assets/images/default-news.jpg'; ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($berita['title'] ?? 'Berita'); ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-primary">Berita</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($berita['title'] ?? 'Judul Berita'); ?></h5>
                        <p class="card-text text-muted mb-3"><?php echo htmlspecialchars(truncateText($berita['excerpt'] ?? $berita['content'] ?? 'Deskripsi berita', 120)); ?></p>
                        <div class="card-meta mb-3">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo formatDate($berita['created_at'] ?? ''); ?>
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 p-4 pt-0">
                        <a href="berita-detail.php?id=<?php echo $berita['id'] ?? '#'; ?>" class="btn btn-primary w-100">
                            <i class="fas fa-arrow-right me-2"></i>Baca Selengkapnya
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="berita.php" class="btn btn-outline-primary btn-lg px-5">
                <i class="fas fa-newspaper me-2"></i>Lihat Semua Berita
            </a>
        </div>
    </div>
</section>
<?php else: ?>
<!-- No Berita Message -->
<section class="section py-5">
    <div class="container text-center">
        <div class="alert alert-info">
            <i class="fas fa-info-circle fa-2x mb-3"></i>
            <h4>Belum Ada Berita</h4>
            <p>Belum ada berita yang dipublikasikan. Silakan kunjungi kembali nanti.</p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Pengumuman Section -->
<?php if (!empty($pengumuman)): ?>
<section class="section bg-light py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title display-5 fw-bold text-warning mb-3">Pengumuman Penting</h2>
            <p class="section-subtitle lead text-muted">Informasi penting yang perlu diketahui oleh siswa dan orang tua</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($pengumuman as $peng): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card pengumuman-card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-bullhorn fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($peng['title']); ?></h5>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo formatDate($peng['created_at']); ?>
                                </small>
                            </div>
                        </div>
                        <p class="card-text text-muted mb-4"><?php echo htmlspecialchars(truncateText($peng['content'], 100)); ?></p>
                        <div class="card-footer bg-transparent border-0 p-0">
                            <a href="pengumuman.php" class="btn btn-warning w-100">
                                <i class="fas fa-eye me-2"></i>Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="pengumuman.php" class="btn btn-outline-warning btn-lg px-5">
                <i class="fas fa-bullhorn me-2"></i>Lihat Semua Pengumuman
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Karya Siswa Section -->
<?php if (!empty($recentKarya)): ?>
<section class="section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title display-5 fw-bold text-success mb-3">Karya Siswa</h2>
            <p class="section-subtitle lead text-muted">Kreativitas dan inovasi siswa SMA Al-Kholis dalam berbagai bidang</p>
        </div>
        
        <div class="row g-4">
            <?php foreach (array_slice($recentKarya, 0, 3) as $karya): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card karya-card h-100 border-0 shadow-sm hover-lift">
                    <div class="position-relative">
                        <img src="<?php echo !empty($karya['image']) ? 'uploads/karya/' . $karya['image'] : 'assets/images/default-artwork.jpg'; ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($karya['title'] ?? 'Karya Siswa'); ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-success">Karya</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($karya['title'] ?? 'Judul Karya'); ?></h5>
                        <p class="card-text text-muted mb-3"><?php echo htmlspecialchars(truncateText($karya['description'] ?? 'Deskripsi karya', 100)); ?></p>
                        <div class="card-meta mb-3">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($karya['author'] ?? 'Siswa'); ?>
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 p-4 pt-0">
                        <a href="karya-siswa.php" class="btn btn-success w-100">
                            <i class="fas fa-eye me-2"></i>Lihat Karya
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="karya-siswa.php" class="btn btn-outline-success btn-lg px-5">
                <i class="fas fa-palette me-2"></i>Lihat Semua Karya
            </a>
        </div>
    </div>
</section>
<?php else: ?>
<!-- No Karya Message -->
<section class="section py-5">
    <div class="container text-center">
        <div class="alert alert-info">
            <i class="fas fa-palette fa-2x mb-3"></i>
            <h4>Belum Ada Karya Siswa</h4>
            <p>Belum ada karya siswa yang dipublikasikan. Silakan kunjungi kembali nanti.</p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Guru Section -->
<?php if (!empty($activeGuru)): ?>
<section class="section bg-light py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title display-5 fw-bold text-info mb-3">Guru Kami</h2>
            <p class="section-subtitle lead text-muted">Tenaga pengajar berkualitas yang siap membimbing siswa menuju kesuksesan</p>
        </div>
        
        <div class="row g-4">
            <?php foreach (array_slice($activeGuru, 0, 4) as $guru): ?>
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 text-center border-0 shadow-sm hover-lift">
                    <div class="card-body">
                        <img src="<?php echo !empty($guru['image']) ? $guru['image'] : 'assets/images/default-avatar.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($guru['nama'] ?? 'Guru'); ?>">
                        <h6 class="card-title fw-bold mb-2 mt-3"><?php echo htmlspecialchars($guru['nama'] ?? 'Nama Guru'); ?></h6>
                        <p class="card-text text-muted small mb-2"><?php echo htmlspecialchars($guru['mata_pelajaran'] ?? 'Mata Pelajaran'); ?></p>
                        <div class="guru-qualification">
                            <span class="badge bg-info text-white small"><?php echo htmlspecialchars($guru['jabatan'] ?? 'Guru'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="guru.php" class="btn btn-outline-info btn-lg px-5">
                <i class="fas fa-chalkboard-teacher me-2"></i>Lihat Semua Guru
            </a>
        </div>
    </div>
</section>
<?php else: ?>
<!-- No Guru Message -->
<section class="section bg-light py-5">
    <div class="container text-center">
        <div class="alert alert-info">
            <i class="fas fa-chalkboard-teacher fa-2x mb-3"></i>
            <h4>Belum Ada Data Guru</h4>
            <p>Belum ada data guru yang tersedia. Silakan kunjungi kembali nanti.</p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Galeri Section -->
<?php if (!empty($recentGaleri)): ?>
<section class="section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title display-5 fw-bold text-secondary mb-3">Galeri Kegiatan</h2>
            <p class="section-subtitle lead text-muted">Dokumentasi berbagai kegiatan dan prestasi SMA Al-Kholis</p>
        </div>
        
        <div class="row g-4">
            <?php foreach (array_slice($recentGaleri, 0, 6) as $galeri): ?>
            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="gallery-item position-relative overflow-hidden rounded-3 shadow-sm hover-lift">
                    <img src="<?php echo !empty($galeri['image']) ? $galeri['image'] : 'assets/images/default-gallery.jpg'; ?>" 
                         class="img-fluid w-100" alt="<?php echo htmlspecialchars($galeri['title'] ?? 'Galeri'); ?>"
                         style="height: 250px; object-fit: cover; cursor: pointer; transition: transform 0.3s ease;"
                         onclick="openLightbox(this.src, '<?php echo htmlspecialchars($galeri['title'] ?? 'Galeri'); ?>')"
                         onmouseover="this.style.transform='scale(1.05)'"
                         onmouseout="this.style.transform='scale(1)'">
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white" 
                         style="background: linear-gradient(transparent, rgba(0,0,0,0.7));">
                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($galeri['title'] ?? 'Galeri'); ?></h6>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="galeri.php" class="btn btn-outline-secondary btn-lg px-5">
                <i class="fas fa-images me-2"></i>Lihat Semua Foto
            </a>
        </div>
    </div>
</section>
<?php else: ?>
<!-- No Galeri Message -->
<section class="section py-5">
    <div class="container text-center">
        <div class="alert alert-info">
            <i class="fas fa-images fa-2x mb-3"></i>
            <h4>Belum Ada Galeri</h4>
            <p>Belum ada foto galeri yang tersedia. Silakan kunjungi kembali nanti.</p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Call to Action Section -->
<section class="section bg-gradient-primary text-white py-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="section-title display-4 fw-bold text-white mb-4">Bergabunglah dengan Kami</h2>
                <p class="section-subtitle lead text-white-50 mb-5">
                    Daftarkan putra-putri Anda untuk mendapatkan pendidikan terbaik di SMA Al-Kholis
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-4">
                    <a href="kontak.php" class="btn btn-light btn-lg px-5 py-3 fw-bold">
                        <i class="fas fa-phone me-2"></i>Hubungi Kami
                    </a>
                    <a href="profil-sekolah.php" class="btn btn-outline-light btn-lg px-5 py-3 fw-bold">
                        <i class="fas fa-info-circle me-2"></i>Pelajari Lebih Lanjut
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop" aria-label="Kembali ke atas">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lightboxTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="lightboxImage" src="" alt="" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

    <style>
        /* Custom styles for better layout */
        .hover-lift {
            transition: all 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
        }
        
        .section {
            padding: 4rem 0;
        }
        
        .card {
            transition: all 0.3s ease;
        }
        
        .gallery-item img:hover {
            transform: scale(1.05);
        }
        
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }
        
        .back-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .feature-item {
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            transform: translateY(-5px);
        }
        
        .guru-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .guru-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .guru-avatar {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .guru-avatar img {
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        
        .guru-avatar img:hover {
            transform: scale(1.05);
        }
    </style>

    <script>
    // Lightbox functionality
    function openLightbox(imageSrc, title) {
        try {
            const lightboxImage = document.getElementById('lightboxImage');
            const lightboxTitle = document.getElementById('lightboxTitle');
            
            if (lightboxImage && lightboxTitle) {
                lightboxImage.src = imageSrc;
                lightboxTitle.textContent = title || 'Galeri';
                new bootstrap.Modal(document.getElementById('lightboxModal')).show();
            }
        } catch (error) {
            console.error('Error opening lightbox:', error);
        }
    }

    // Back to top functionality
    window.addEventListener('scroll', function() {
        try {
            const backToTop = document.getElementById('backToTop');
            if (backToTop) {
                if (window.scrollY > 300) {
                    backToTop.classList.add('show');
                } else {
                    backToTop.classList.remove('show');
                }
            }
        } catch (error) {
            console.error('Error in scroll handler:', error);
        }
    });

    // Back to top click handler
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const backToTop = document.getElementById('backToTop');
            if (backToTop) {
                backToTop.addEventListener('click', function() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
        } catch (error) {
            console.error('Error setting up back to top:', error);
        }
    });
    
    // Add animation on scroll
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
    
    // Observe all cards and feature items (only if they exist)
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const items = document.querySelectorAll('.card, .feature-item');
            if (items.length > 0) {
                items.forEach(item => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(30px)';
                    item.style.transition = 'all 0.6s ease';
                    observer.observe(item);
                });
            }
        } catch (error) {
            console.error('Error setting up animations:', error);
        }
    });
    </script>

<?php require_once 'includes/footer.php'; ?> 