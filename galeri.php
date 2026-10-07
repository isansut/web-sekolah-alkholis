<?php
$pageTitle = 'Galeri Kegiatan';
require_once 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Filter by activity category
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$whereClause = "WHERE status = 'active'";
$params = [];

if (!empty($category)) {
    $whereClause .= " AND category = ?";
    $params[] = $category;
}

try {
    $pdo = getDBConnection();
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM galeri $whereClause");
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch()['total'];
    
    // Get galeri with pagination
    $stmt = $pdo->prepare("
        SELECT * FROM galeri 
        $whereClause 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute(array_merge($params, [$perPage, $offset]));
    $galeri = $stmt->fetchAll();
    
    // Get activity categories for filter
    $catStmt = $pdo->query("SELECT DISTINCT category FROM galeri WHERE status = 'active' ORDER BY category");
    $categories = $catStmt->fetchAll();
    
    // Pagination data
    $pagination = getPagination($totalRecords, $perPage, $page);
    
} catch (Exception $e) {
    $galeri = [];
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
                <h1 class="section-title text-white mb-3">Galeri Kegiatan</h1>
                <p class="section-subtitle text-white-50">
                    Dokumentasi kegiatan dan acara SMA Al-Kholis
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
                <h5 class="mb-0 text-dark fw-semibold">Filter Kegiatan:</h5>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="galeri.php" class="btn btn-outline-primary <?php echo empty($category) ? 'active' : ''; ?>">
                        Semua Kegiatan
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="galeri.php?category=<?php echo urlencode($cat['category']); ?>" 
                       class="btn btn-outline-primary <?php echo $category === $cat['category'] ? 'active' : ''; ?>">
                        <?php echo ucfirst($cat['category']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Galeri Section -->
<section class="section">
    <div class="container">
        <?php if (!empty($category)): ?>
        <div class="mb-4">
            <h4 class="text-dark">Kategori: <?php echo ucfirst($category); ?></h4>
            <p class="text-muted">Ditemukan <?php echo $totalRecords; ?> foto kegiatan</p>
        </div>
        <?php endif; ?>
        
        <?php if (empty($galeri)): ?>
        <div class="text-center py-5">
            <i class="fas fa-images fa-4x text-muted mb-3"></i>
            <h4 class="text-dark">Tidak ada foto kegiatan ditemukan</h4>
            <p class="text-muted">
                <?php if (!empty($category)): ?>
                Coba pilih kategori kegiatan lain atau lihat semua kegiatan.
                <?php else: ?>
                Belum ada foto kegiatan yang dipublikasikan.
                <?php endif; ?>
            </p>
            <a href="galeri.php" class="btn btn-primary">Lihat Semua Kegiatan</a>
        </div>
        <?php else: ?>
        
        <div class="row g-4">
            <?php foreach ($galeri as $item): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card h-100">
                    <div class="gallery-item" onclick="openLightbox('<?php echo $item['image'] ? $item['image'] : 'assets/images/default-galeri.jpg'; ?>', '<?php echo htmlspecialchars($item['title']); ?>')">
                        <img src="<?php echo $item['image'] ? $item['image'] : 'assets/images/default-galeri.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($item['title']); ?>" 
                             class="galeri-image">
                        <div class="overlay">
                            <div class="overlay-content">
                                <i class="fas fa-search-plus fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h6>
                        <?php if (!empty($item['description'])): ?>
                        <p class="card-text small"><?php echo truncateText($item['description'], 80); ?></p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge badge-secondary"><?php echo ucfirst($item['category']); ?></span>
                            <small class="text-muted"><?php echo formatDate($item['created_at']); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if (!empty($pagination) && $pagination['total'] > 1): ?>
        <nav aria-label="Galeri pagination" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php if (isset($pagination['prev'])): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['prev']; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php foreach ($pagination['pages'] as $pageNum): ?>
                <li class="page-item <?php echo $pageNum == $pagination['current'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $pageNum; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>">
                        <?php echo $pageNum; ?>
                    </a>
                </li>
                <?php endforeach; ?>
                
                <?php if (isset($pagination['next'])): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['next']; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>">
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
                <h3 class="text-dark">Kategori Kegiatan</h3>
                <p class="section-subtitle mb-4">Berbagai jenis kegiatan yang dapat Anda lihat di galeri kami:</p>
                
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="feature-item text-center">
                            <div class="feature-icon mb-3" style="width: 60px; height: 60px; font-size: 1.5rem; margin: 0 auto;">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h6 class="feature-title">Akademik</h6>
                            <p class="text-muted">Kegiatan pembelajaran dan prestasi akademik</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="feature-item text-center">
                            <div class="feature-icon mb-3" style="width: 60px; height: 60px; font-size: 1.5rem; margin: 0 auto;">
                                <i class="fas fa-futbol"></i>
                            </div>
                            <h6 class="feature-title">Olahraga</h6>
                            <p class="text-muted">Kegiatan olahraga dan turnamen</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="feature-item text-center">
                            <div class="feature-icon mb-3" style="width: 60px; height: 60px; font-size: 1.5rem; margin: 0 auto;">
                                <i class="fas fa-palette"></i>
                            </div>
                            <h6 class="feature-title">Seni & Budaya</h6>
                            <p class="text-muted">Pentas seni dan kegiatan budaya</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="feature-item text-center">
                            <div class="feature-icon mb-3" style="width: 60px; height: 60px; font-size: 1.5rem; margin: 0 auto;">
                                <i class="fas fa-users"></i>
                            </div>
                            <h6 class="feature-title">Ekstrakurikuler</h6>
                            <p class="text-muted">Kegiatan ekstrakurikuler dan organisasi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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

<script>
// Lightbox functionality
function openLightbox(imageSrc, title) {
    document.getElementById('lightboxImage').src = imageSrc;
    document.getElementById('lightboxTitle').textContent = title;
    new bootstrap.Modal(document.getElementById('lightboxModal')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?> 