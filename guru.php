<?php
$pageTitle = 'Tim Pengajar';
require_once 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

try {
    $pdo = getDBConnection();
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM guru WHERE status = 'active'");
    $countStmt->execute();
    $totalRecords = $countStmt->fetch()['total'];
    
    // Get guru with pagination
    $stmt = $pdo->prepare("
        SELECT * FROM guru 
        WHERE status = 'active'
        ORDER BY nama ASC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$perPage, $offset]);
    $guru = $stmt->fetchAll();
    
    // Pagination data
    $pagination = getPagination($totalRecords, $perPage, $page);
    
} catch (Exception $e) {
    $guru = [];
    $pagination = [];
    $totalRecords = 0;
}
?>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="section-title text-white mb-3">Tim Pengajar</h1>
                <p class="section-subtitle text-white-50">
                    Kenali tim pengajar profesional dan berpengalaman SMA Al-Kholis
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Guru Section -->
<section class="section">
    <div class="container">
        <?php if (empty($guru)): ?>
        <div class="text-center py-5">
            <i class="fas fa-chalkboard-teacher fa-4x text-muted mb-3"></i>
            <h4 class="text-dark">Tidak ada guru ditemukan</h4>
            <p class="text-muted">
                Belum ada data guru yang dipublikasikan.
            </p>
            <a href="guru.php" class="btn btn-primary">Lihat Semua Guru</a>
        </div>
        <?php else: ?>
        
        <div class="row g-4">
            <?php foreach ($guru as $g): ?>
            <div class="col-md-3 col-sm-6">
                <div class="card h-100">
                    <?php if (!empty($g['image'])): ?>
                    <img src="<?php echo $g['image'] ? $g['image'] : 'assets/images/default-guru.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($g['nama']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h6 class="card-title mb-1 text-dark"><?php echo htmlspecialchars($g['nama']); ?></h6>
                        <div class="text-muted small mb-1">
                            <i class="fas fa-chalkboard-teacher me-1"></i>
                            <?php echo htmlspecialchars($g['jabatan'] ?? 'Guru'); ?>
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-book me-1"></i>
                            <?php echo htmlspecialchars($g['mata_pelajaran']); ?>
                        </div>
                        <?php if (!empty($g['bio'])): ?>
                        <div class="text-muted small mt-2">
                            <?php echo truncateText($g['bio'], 60); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100" 
                                data-bs-toggle="modal" data-bs-target="#guruModal<?php echo $g['id']; ?>">
                            <i class="fas fa-info-circle me-1"></i>Detail
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Modal for guru detail -->
            <div class="modal fade" id="guruModal<?php echo $g['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Guru - <?php echo htmlspecialchars($g['nama']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <?php if (!empty($g['image'])): ?>
                                    <img src="<?php echo $g['image'] ? $g['image'] : 'assets/images/default-guru.jpg'; ?>" 
                                         class="img-fluid rounded border-2 border-primary mb-3" alt="<?php echo htmlspecialchars($g['nama']); ?>"
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3 border-2 border-primary" style="width: 100px; height: 100px;">
                                        <i class="fas fa-user-tie fa-2x text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <h4 class="text-dark"><?php echo htmlspecialchars($g['nama']); ?></h4>
                                    <p class="text-muted"><?php echo htmlspecialchars($g['jabatan'] ?? 'Guru'); ?></p>
                                    
                                    <div class="mb-3">
                                        <strong class="text-dark">Mata Pelajaran:</strong><br>
                                        <?php echo htmlspecialchars($g['mata_pelajaran']); ?>
                                    </div>
                                    
                                    <?php if (!empty($g['bio'])): ?>
                                    <div class="mb-3">
                                        <strong class="text-dark">Biografi:</strong><br>
                                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($g['bio'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <strong class="text-dark">Pendidikan:</strong><br>
                                        <span class="badge badge-primary"><?php echo htmlspecialchars($g['pendidikan'] ?? 'S1'); ?></span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong class="text-dark">Status:</strong><br>
                                        <span class="badge badge-success">Aktif</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if (!empty($pagination) && $pagination['total'] > 1): ?>
        <nav aria-label="Guru pagination" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php if (isset($pagination['prev'])): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['prev']; ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php foreach ($pagination['pages'] as $pageNum): ?>
                <li class="page-item <?php echo $pageNum == $pagination['current'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $pageNum; ?>">
                        <?php echo $pageNum; ?>
                    </a>
                </li>
                <?php endforeach; ?>
                
                <?php if (isset($pagination['next'])): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['next']; ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
</section>

<!-- Info Section -->
<section class="section bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h3 class="text-dark">Tim Pengajar Profesional</h3>
                <p class="section-subtitle mb-4">SMA Al-Kholis memiliki tim pengajar yang kompeten dan berpengalaman dalam mendidik siswa:</p>
                
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="feature-item text-center">
                            <div class="feature-icon mb-3" style="width: 60px; height: 60px; font-size: 1.5rem; margin: 0 auto;">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h6 class="feature-title">Berpengalaman</h6>
                            <p class="text-muted">Guru dengan pengalaman mengajar bertahun-tahun</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-item text-center">
                            <div class="feature-icon mb-3" style="width: 60px; height: 60px; font-size: 1.5rem; margin: 0 auto;">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <h6 class="feature-title">Berkualifikasi</h6>
                            <p class="text-muted">Guru dengan kualifikasi pendidikan yang memadai</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-item text-center">
                            <div class="feature-icon mb-3" style="width: 60px; height: 60px; font-size: 1.5rem; margin: 0 auto;">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h6 class="feature-title">Dedikasi Tinggi</h6>
                            <p class="text-muted">Guru yang berdedikasi dalam mendidik siswa</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 

<style>
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
    
    .guru-avatar .bg-light {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .card-footer {
        background: transparent;
        border-top: 1px solid rgba(0,0,0,0.1);
    }
    
    .badge {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
    }
</style> 