<?php
$pageTitle = 'Berita';
require_once 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 6;
$offset = ($page - 1) * $perPage;

// Search functionality
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$whereClause = "WHERE status = 'published'";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
    $searchParam = "%$search%";
    $params = [$searchParam, $searchParam, $searchParam];
}

try {
    $pdo = getDBConnection();
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM berita $whereClause");
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch()['total'];
    
    // Get berita with pagination - explicitly select only needed fields
    $stmt = $pdo->prepare("
        SELECT id, title, content, excerpt, image, author, status, created_at 
        FROM berita 
        $whereClause 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute(array_merge($params, [$perPage, $offset]));
    $berita = $stmt->fetchAll();
    
    // Pagination data
    $pagination = getPagination($totalRecords, $perPage, $page);
    
} catch (Exception $e) {
    $berita = [];
    $pagination = [];
    $totalRecords = 0;
    error_log("Database error in berita.php: " . $e->getMessage());
}
?>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="section-title text-white mb-3">Berita Sekolah</h1>
                <p class="section-subtitle text-white-50">
                    Informasi terbaru seputar kegiatan dan prestasi SMA Al-Kholis
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="py-4 bg-light">
    <div class="container">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Cari berita..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <a href="berita.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-refresh me-2"></i>Reset Pencarian
                </a>
            </div>
        </form>
    </div>
</section>

<!-- Berita Section -->
<section class="section">
    <div class="container">
        <?php if (!empty($search)): ?>
        <div class="mb-4">
            <h4 class="text-dark">Hasil Pencarian: "<?php echo htmlspecialchars($search); ?>"</h4>
            <p class="text-muted">Ditemukan <?php echo $totalRecords; ?> berita</p>
        </div>
        <?php endif; ?>
        
        <?php if (empty($berita)): ?>
        <div class="text-center py-5">
            <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
            <h4 class="text-dark">Tidak ada berita ditemukan</h4>
            <p class="text-muted">
                <?php if (!empty($search)): ?>
                Coba ubah kata kunci pencarian Anda.
                <?php else: ?>
                Belum ada berita yang dipublikasikan.
                <?php endif; ?>
            </p>
            <?php if (!empty($search)): ?>
            <a href="berita.php" class="btn btn-primary">Lihat Semua Berita</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        
        <div class="row g-4">
            <?php foreach ($berita as $item): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card berita-card h-100">
                    <?php 
                    // Clean and validate image data
                    $imageFile = trim($item['image'] ?? '');
                    $imagePath = '';
                    $imageExists = false;
                    
                    if (!empty($imageFile) && is_string($imageFile)) {
                        // Handle both relative and absolute paths
                        if (strpos($imageFile, '/') === 0) {
                            // Absolute path from root (e.g., /alkholis/uploads/berita/filename.jpg)
                            $imagePath = $imageFile;
                            $imageExists = true; // Assume it exists if it's a valid path
                        } else {
                            // Relative path (e.g., filename.jpg)
                            $imagePath = 'uploads/berita/' . $imageFile;
                            $imageExists = file_exists($imagePath) && is_file($imagePath);
                        }
                    }
                    ?>
                    
                    <?php if ($imageExists): ?>
                    <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($item['title'] ?? 'Judul Berita'); ?></h5>
                        <p class="card-text">
                            <?php 
                            // Use excerpt if available and different from content, otherwise use truncated content
                            $excerpt = '';
                            if (!empty($item['excerpt']) && trim($item['excerpt']) !== trim($item['content'])) {
                                $excerpt = $item['excerpt'];
                            } else {
                                $excerpt = $item['content'] ?? '';
                            }
                            echo htmlspecialchars(truncateText($excerpt, 120)); 
                            ?>
                        </p>
                        <div class="card-meta">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($item['author'] ?? 'Admin'); ?>
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo formatDate($item['created_at'] ?? date('Y-m-d')); ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <a href="berita-detail.php?id=<?php echo (int)($item['id'] ?? 0); ?>" 
                           class="btn btn-primary btn-sm w-100">
                            Baca Selengkapnya
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if (!empty($pagination) && $pagination['total'] > 1): ?>
        <nav aria-label="Berita pagination" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php if (isset($pagination['prev'])): ?>
                <li class="page-item">
                    <a class="page-link" href="berita.php?page=<?php echo $pagination['prev']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>

                <?php foreach ($pagination['pages'] as $pageNum): ?>
                <li class="page-item <?php echo $pageNum === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="berita.php?page=<?php echo $pageNum; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                        <?php echo $pageNum; ?>
                    </a>
                </li>
                <?php endforeach; ?>

                <?php if (isset($pagination['next'])): ?>
                <li class="page-item">
                    <a class="page-link" href="berita.php?page=<?php echo $pagination['next']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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

<!-- Call to Action -->
<section class="section bg-light">
    <div class="container text-center">
        <h3 class="text-dark">Ingin Berita Terbaru?</h3>
        <p class="section-subtitle mb-4">Dapatkan informasi terbaru seputar kegiatan sekolah langsung di email Anda.</p>
        <a href="kontak.php" class="btn btn-primary btn-lg">
            <i class="fas fa-envelope me-2"></i>Hubungi Kami
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 