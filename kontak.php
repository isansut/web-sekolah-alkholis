<?php
$pageTitle = 'Kontak';
require_once 'includes/header.php';

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subjek = sanitize($_POST['subjek'] ?? '');
    $pesan = sanitize($_POST['pesan'] ?? '');
    
    if (empty($nama) || empty($email) || empty($subjek) || empty($pesan)) {
        $message = 'Mohon lengkapi semua field yang diperlukan.';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Format email tidak valid.';
        $messageType = 'danger';
    } else {
        // Here you would typically save to database or send email
        $message = 'Pesan Anda telah berhasil dikirim. Kami akan segera menghubungi Anda.';
        $messageType = 'success';
        
        // Reset form
        $_POST = [];
    }
}
?>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="section-title text-white mb-3">Hubungi Kami</h1>
                <p class="section-subtitle text-white-50">
                    Kami siap membantu dan menjawab pertanyaan Anda seputar SMA Al-Kholis
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Information Section -->
<section class="section">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Info -->
            <div class="col-lg-4">
                <div class="contact-info">
                    <h3 class="mb-4">Informasi Kontak</h3>
                    
                    <div class="contact-item mb-4">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-details">
                            <h5>Alamat</h5>
                            <p><?php echo getSetting('contact_address'); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-details">
                            <h5>Telepon</h5>
                            <p>
                                <a href="tel:<?php echo getSetting('contact_phone'); ?>" class="text-decoration-none">
                                    <?php echo getSetting('contact_phone'); ?>
                                </a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <h5>Email</h5>
                            <p>
                                <a href="mailto:<?php echo getSetting('contact_email'); ?>" class="text-decoration-none">
                                    <?php echo getSetting('contact_email'); ?>
                                </a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-details">
                            <h5>Jam Operasional</h5>
                            <p>Senin - Jumat: 07:00 - 15:00<br>
                               Sabtu: 07:00 - 12:00</p>
                        </div>
                    </div>
                    
                    <div class="social-links mt-4">
                        <h5 class="mb-3">Ikuti Kami</h5>
                        <div class="social-icons">
                            <a href="https://facebook.com/alkholis" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="Facebook SMA IT AL-KHOLIS" title="Facebook SMA IT AL-KHOLIS">
                                <i class="fab fa-facebook"></i>
                            </a>
                            <a href="https://instagram.com/alkholis" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="Instagram SMA IT AL-KHOLIS" title="Instagram SMA IT AL-KHOLIS">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://youtube.com/@alkholis" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="YouTube SMA IT AL-KHOLIS" title="YouTube SMA IT AL-KHOLIS">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="https://wa.me/6281234567890" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp SMA IT AL-KHOLIS" title="WhatsApp SMA IT AL-KHOLIS">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="contact-form">
                    <h3 class="mb-4">Kirim Pesan</h3>
                    
                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="nama" 
                                           name="nama" 
                                           value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>"
                                           required>
                                    <div class="invalid-feedback">
                                        Mohon masukkan nama lengkap Anda.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                           required>
                                    <div class="invalid-feedback">
                                        Mohon masukkan email yang valid.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="subjek" class="form-label">Subjek <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="subjek" 
                                           name="subjek" 
                                           value="<?php echo htmlspecialchars($_POST['subjek'] ?? ''); ?>"
                                           required>
                                    <div class="invalid-feedback">
                                        Mohon masukkan subjek pesan.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="pesan" class="form-label">Pesan <span class="text-danger">*</span></label>
                                    <textarea class="form-control" 
                                              id="pesan" 
                                              name="pesan" 
                                              rows="5" 
                                              required><?php echo htmlspecialchars($_POST['pesan'] ?? ''); ?></textarea>
                                    <div class="invalid-feedback">
                                        Mohon masukkan pesan Anda.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="section bg-light">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Lokasi Kami</h2>
            <p class="section-subtitle">Kunjungi SMA Al-Kholis untuk melihat fasilitas dan lingkungan belajar kami</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="map-container">
                    <div class="map-placeholder">
                        <div class="map-content text-center">
                            <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
                            <h4>Peta Lokasi</h4>
                            <p class="text-muted mb-3">
                                <?php echo getSetting('contact_address'); ?>
                            </p>
                            <a href="https://maps.google.com" target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-external-link-alt me-2"></i>Buka di Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Pertanyaan Umum</h2>
            <p class="section-subtitle">Temukan jawaban untuk pertanyaan yang sering diajukan</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq1">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                Bagaimana cara mendaftar ke SMA Al-Kholis?
                            </button>
                        </h2>
                        <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Pendaftaran dapat dilakukan secara online melalui website kami atau datang langsung ke sekolah. 
                                Persyaratan dan jadwal pendaftaran akan diumumkan setiap tahun ajaran baru.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                Apakah ada program beasiswa untuk siswa berprestasi?
                            </button>
                        </h2>
                        <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Ya, kami menyediakan berbagai program beasiswa untuk siswa berprestasi baik di bidang akademik 
                                maupun non-akademik. Informasi detail dapat diperoleh melalui bagian administrasi sekolah.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                Bagaimana dengan fasilitas olahraga dan ekstrakurikuler?
                            </button>
                        </h2>
                        <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                SMA Al-Kholis dilengkapi dengan berbagai fasilitas olahraga seperti lapangan basket, futsal, 
                                dan ruang fitness. Kami juga menyediakan berbagai ekstrakurikuler untuk mengembangkan 
                                bakat dan minat siswa.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA Section -->
<section class="section bg-gradient-primary text-white">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="section-title text-white mb-4">Masih Ada Pertanyaan?</h2>
                <p class="section-subtitle text-white-50 mb-4">
                    Jangan ragu untuk menghubungi kami. Tim kami siap membantu Anda 24/7.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="tel:<?php echo getSetting('contact_phone'); ?>" class="btn btn-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Telepon Sekarang
                    </a>
                    <a href="mailto:<?php echo getSetting('contact_email'); ?>" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-envelope me-2"></i>Kirim Email
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<style>
/* Custom styles for contact page */
.contact-info {
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    height: 100%;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.contact-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #1e40af, #06b6d4);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.contact-details h5 {
    margin-bottom: 0.5rem;
    color: #1e40af;
    font-weight: 600;
}

.contact-details p {
    margin-bottom: 0;
    color: #64748b;
}

/* Social Media Styling */
.social-links {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.social-links h5 {
    margin-bottom: 1rem;
    color: #1e40af;
    font-weight: 600;
}

.social-icons {
    display: flex;
    flex-direction: row;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.social-link {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #1e40af, #06b6d4);
    color: white;
    text-decoration: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    font-size: 1.3rem;
    box-shadow: 0 2px 10px rgba(30, 64, 175, 0.2);
}

.social-link:hover {
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    color: white;
    text-decoration: none;
}

.social-link i {
    font-size: 1.3rem;
}

/* Contact Form Styling */
.contact-form {
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-control {
    border: 2px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #1e40af;
    box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #1e40af, #06b6d4);
    border: none;
    border-radius: 0.75rem;
    padding: 0.75rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1e3a8a, #0891b2);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(30, 64, 175, 0.3);
}

/* FAQ Section */
.accordion-item {
    border: none;
    border-radius: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.accordion-button {
    border-radius: 1rem;
    font-weight: 600;
    color: #1e40af;
}

.accordion-button:not(.collapsed) {
    background: linear-gradient(135deg, #1e40af, #06b6d4);
    color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .contact-info,
    .contact-form {
        padding: 1.5rem;
    }
    
    .social-icons {
        justify-content: center;
        gap: 0.75rem;
    }
    
    .social-link {
        width: 45px;
        height: 45px;
        font-size: 1.2rem;
    }
    
    .contact-item {
        flex-direction: column;
        text-align: center;
    }
    
    .contact-icon {
        margin: 0 auto;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?> 