<?php
$pageTitle = '500 - Server Error';
require_once 'includes/header.php';
?>

<!-- 500 Error Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="error-page">
                    <h1 class="display-1 text-danger">500</h1>
                    <h2 class="mb-4">Server Error</h2>
                    <p class="lead mb-4">
                        Maaf, terjadi kesalahan pada server. Tim kami sedang memperbaiki masalah ini.
                    </p>
                    
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <p class="text-muted">
                            Silakan coba lagi dalam beberapa menit atau hubungi kami jika masalah berlanjut.
                        </p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="index.php" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-home me-2"></i>Kembali ke Beranda
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="kontak.php" class="btn btn-outline-danger btn-lg w-100">
                                <i class="fas fa-envelope me-2"></i>Laporkan Masalah
                            </a>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Informasi</h6>
                        <p class="mb-0">
                            Jika Anda melihat halaman ini berulang kali, silakan hubungi administrator website 
                            atau kirim email ke: <strong>admin@alkholis.sch.id</strong>
                        </p>
                    </div>
                    
                    <div class="mt-4">
                        <button onclick="location.reload()" class="btn btn-outline-secondary">
                            <i class="fas fa-refresh me-2"></i>Refresh Halaman
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.error-page {
    padding: 3rem 0;
}

.error-page .display-1 {
    font-size: 8rem;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .error-page .display-1 {
        font-size: 6rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?> 