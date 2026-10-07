<?php
$pageTitle = '404 - Halaman Tidak Ditemukan';
require_once 'includes/header.php';
?>

<!-- 404 Error Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="error-page">
                    <h1 class="display-1 text-primary">404</h1>
                    <h2 class="mb-4">Halaman Tidak Ditemukan</h2>
                    <p class="lead mb-4">
                        Maaf, halaman yang Anda cari tidak ditemukan atau telah dipindahkan.
                    </p>
                    
                    <div class="mb-4">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <p class="text-muted">
                            Mungkin URL yang Anda masukkan salah atau halaman telah dihapus.
                        </p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="index.php" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-home me-2"></i>Kembali ke Beranda
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="kontak.php" class="btn btn-outline-primary btn-lg w-100">
                                <i class="fas fa-envelope me-2"></i>Hubungi Kami
                            </a>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h5 class="mb-3">Halaman Populer</h5>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <a href="berita.php" class="text-decoration-none">
                                <i class="fas fa-newspaper me-1"></i>Berita
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="guru.php" class="text-decoration-none">
                                <i class="fas fa-chalkboard-teacher me-1"></i>Guru
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="karya-siswa.php" class="text-decoration-none">
                                <i class="fas fa-lightbulb me-1"></i>Karya Siswa
                            </a>
                        </div>
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