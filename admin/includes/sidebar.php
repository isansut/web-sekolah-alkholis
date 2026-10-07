<?php
// Get current page for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- Sidebar -->
<div class="col-md-3 col-lg-2 px-0">
    <div class="sidebar p-3">
        <div class="text-center mb-4">
            <h5 class="text-white mb-0">
                <i class="fas fa-graduation-cap me-2"></i>
                Admin Panel
            </h5>
            <small class="text-white-50">SMA Al-Kholis</small>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>" href="index.php">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </a>
            <a class="nav-link <?php echo $currentPage === 'berita' ? 'active' : ''; ?>" href="berita.php">
                <i class="fas fa-newspaper me-2"></i>
                Kelola Berita
            </a>
            <a class="nav-link <?php echo $currentPage === 'guru' ? 'active' : ''; ?>" href="guru.php">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                Kelola Guru
            </a>
            <a class="nav-link <?php echo $currentPage === 'jadwal' ? 'active' : ''; ?>" href="jadwal.php">
                <i class="fas fa-calendar-alt me-2"></i>
                Jadwal Pelajaran
            </a>
            <a class="nav-link <?php echo $currentPage === 'ekstrakurikuler' ? 'active' : ''; ?>" href="ekstrakurikuler.php">
                <i class="fas fa-users me-2"></i>
                Ekstrakurikuler
            </a>
            <a class="nav-link <?php echo $currentPage === 'prestasi' ? 'active' : ''; ?>" href="prestasi.php">
                <i class="fas fa-trophy me-2"></i>
                Prestasi Siswa
            </a>
            <a class="nav-link <?php echo $currentPage === 'karya_siswa' ? 'active' : ''; ?>" href="karya_siswa.php">
                <i class="fas fa-lightbulb me-2"></i>
                Karya Siswa
            </a>
            <a class="nav-link <?php echo $currentPage === 'galeri' ? 'active' : ''; ?>" href="galeri.php">
                <i class="fas fa-images me-2"></i>
                Galeri
            </a>
            <a class="nav-link <?php echo $currentPage === 'siswa' ? 'active' : ''; ?>" href="siswa.php">
                <i class="fas fa-user-graduate me-2"></i>
                Siswa
            </a>
            <a class="nav-link <?php echo $currentPage === 'pengumuman' ? 'active' : ''; ?>" href="pengumuman.php">
                <i class="fas fa-bullhorn me-2"></i>
                Pengumuman
            </a>
            <a class="nav-link <?php echo $currentPage === 'pesan' ? 'active' : ''; ?>" href="pesan.php">
                <i class="fas fa-envelope me-2"></i>
                Pesan Masuk
            </a>
            <a class="nav-link <?php echo $currentPage === 'kontak' ? 'active' : ''; ?>" href="kontak.php">
                <i class="fas fa-address-book me-2"></i>
                Kontak
            </a>
            <a class="nav-link <?php echo $currentPage === 'settings' ? 'active' : ''; ?>" href="settings.php">
                <i class="fas fa-cog me-2"></i>
                Pengaturan
            </a>
            <hr class="text-white-50">
            <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>
                Logout
            </a>
        </nav>
    </div>
</div> 