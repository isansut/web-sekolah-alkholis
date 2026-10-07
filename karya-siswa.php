<?php
$pageTitle = 'Karya Siswa';
require_once 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Filter by category
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$whereClause = "WHERE status = 'approved'";
$params = [];

if (!empty($category)) {
    $whereClause .= " AND category = ?";
    $params[] = $category;
}

try {
    $pdo = getDBConnection();
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM karya_siswa $whereClause");
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch()['total'];
    
    // Get karya with pagination
    $stmt = $pdo->prepare("
        SELECT * FROM karya_siswa 
        $whereClause 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute(array_merge($params, [$perPage, $offset]));
    $karya = $stmt->fetchAll();
    
    // Get categories for filter
    $catStmt = $pdo->query("SELECT DISTINCT category FROM karya_siswa WHERE status = 'approved' ORDER BY category");
    $categories = $catStmt->fetchAll();
    
    // Pagination data
    $pagination = getPagination($totalRecords, $perPage, $page);
    
} catch (Exception $e) {
    $karya = [];
    $categories = [];
    $pagination = [];
    $totalRecords = 0;
}
?>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="section-title text-white mb-3">Karya Siswa</h1>
                <p class="section-subtitle text-white-50">
                    Kreativitas dan inovasi siswa SMA Al-Kholis dalam berbagai bidang
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Filter Section -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0 text-dark fw-semibold">Filter Kategori:</h5>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="karya-siswa.php" class="btn btn-outline-primary <?php echo empty($category) ? 'active' : ''; ?>">
                        Semua
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="karya-siswa.php?category=<?php echo urlencode($cat['category']); ?>" 
                       class="btn btn-outline-primary <?php echo $category === $cat['category'] ? 'active' : ''; ?>">
                        <?php echo ucfirst($cat['category']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Karya Section -->
<section class="section">
    <div class="container">
        <?php if (!empty($category)): ?>
        <div class="mb-4">
            <h4 class="text-dark">Kategori: <?php echo ucfirst($category); ?></h4>
            <p class="text-muted">Ditemukan <?php echo $totalRecords; ?> karya</p>
        </div>
        <?php endif; ?>
        
        <?php if (empty($karya)): ?>
        <div class="text-center py-5">
            <i class="fas fa-lightbulb fa-4x text-muted mb-3"></i>
            <h4 class="text-dark">Tidak ada karya ditemukan</h4>
            <p class="text-muted">
                <?php if (!empty($category)): ?>
                Coba pilih kategori lain atau lihat semua karya.
                <?php else: ?>
                Belum ada karya siswa yang dipublikasikan.
                <?php endif; ?>
            </p>
            <a href="karya-siswa.php" class="btn btn-primary">Lihat Semua Karya</a>
        </div>
        <?php else: ?>
        
        <div class="row g-4">
            <?php foreach ($karya as $item): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card karya-card h-100">
                    <?php if (!empty($item['image']) && file_exists('uploads/karya/' . $item['image'])): ?>
                    <img src="uploads/karya/<?php echo htmlspecialchars($item['image']); ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge badge-primary"><?php echo ucfirst($item['category']); ?></span>
                            <small class="text-muted"><?php echo $item['year'] ?? date('Y'); ?></small>
                        </div>
                        
                        <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                        <p class="card-text"><?php echo truncateText($item['description'], 120); ?></p>
                        
                        <div class="card-meta">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($item['student_name'] ?? $item['author'] ?? 'Siswa'); ?>
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo formatDate($item['created_at']); ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary btn-sm w-100" 
                                data-bs-toggle="modal" 
                                data-bs-target="#karyaModal<?php echo $item['id']; ?>">
                            <i class="fas fa-eye me-2"></i>Lihat Detail
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Modal for karya detail -->
            <div class="modal fade karya-modal" id="karyaModal<?php echo $item['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                                                <?php if (!empty($item['image']) && file_exists('uploads/karya/' . $item['image'])): ?>
                    <img src="uploads/karya/<?php echo htmlspecialchars($item['image']); ?>" 
                                 class="img-fluid rounded mb-3" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php endif; ?>
                            

                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong class="text-dark">Kategori:</strong> <?php echo ucfirst($item['category']); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong class="text-dark">Tahun:</strong> <?php echo $item['year'] ?? date('Y'); ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <strong class="text-dark">Deskripsi:</strong>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                            </div>
                            
                            <?php if (!empty($item['achievement'])): ?>
                            <div class="mb-3">
                                <strong class="text-dark">Pencapaian:</strong>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($item['achievement'])); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="text-muted">
                                <small>Dibuat oleh: <?php echo htmlspecialchars($item['student_name'] ?? $item['author'] ?? 'Siswa'); ?></small>
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
        <nav aria-label="Karya pagination" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php if (isset($pagination['prev'])): ?>
                <li class="page-item">
                    <a class="page-link" href="karya-siswa.php?page=<?php echo $pagination['prev']; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php foreach ($pagination['pages'] as $pageNum): ?>
                <li class="page-item <?php echo $pageNum === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="karya-siswa.php?page=<?php echo $pageNum; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>">
                        <?php echo $pageNum; ?>
                    </a>
                </li>
                <?php endforeach; ?>
                
                <?php if (isset($pagination['next'])): ?>
                <li class="page-item">
                    <a class="page-link" href="karya-siswa.php?page=<?php echo $pagination['next']; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>">
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

<?php require_once 'includes/footer.php'; ?> 