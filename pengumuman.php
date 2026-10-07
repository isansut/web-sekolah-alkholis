<?php
$pageTitle = 'Pengumuman';
require_once 'includes/header.php';
require_once 'config/database.php';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Filters
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$target = isset($_GET['target']) ? trim($_GET['target']) : '';

$where = "WHERE status = 'published' AND (expiry_date IS NULL OR expiry_date >= CURDATE())";
$params = [];

if ($target !== '') {
    $where .= " AND target_audience = ?";
    $params[] = $target;
}

if ($q !== '') {
    $where .= " AND (title LIKE ? OR content LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
}

try {
    $pdo = getDBConnection();

    // Total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM pengumuman $where");
    $countStmt->execute($params);
    $totalRecords = (int)($countStmt->fetch()['total'] ?? 0);

    // Data query
    $stmt = $pdo->prepare("SELECT * FROM pengumuman $where ORDER BY priority DESC, created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute(array_merge($params, [$perPage, $offset]));
    $items = $stmt->fetchAll();

    $pagination = getPagination($totalRecords, $perPage, $page);
} catch (Exception $e) {
    $items = [];
    $pagination = [];
    $totalRecords = 0;
}
?>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="section-title text-white mb-3">Pengumuman</h1>
                <p class="section-subtitle text-white-50">
                    Informasi terbaru dan penting dari SMA Al-Kholis
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Filters -->
<section class="py-4 bg-light">
    <div class="container">
        <form class="row g-3 align-items-end" method="get" action="pengumuman.php">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Pencarian</label>
                <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" 
                       class="form-control" placeholder="Cari judul atau isi pengumuman...">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold text-dark">Target</label>
                <select name="target" class="form-select">
                    <option value="" <?php echo $target===''?'selected':'';?>>Semua</option>
                    <option value="siswa" <?php echo $target==='siswa'?'selected':'';?>>Siswa</option>
                    <option value="guru" <?php echo $target==='guru'?'selected':'';?>>Guru</option>
                    <option value="ortu" <?php echo $target==='ortu'?'selected':'';?>>Orang Tua</option>
                    <option value="umum" <?php echo $target==='umum'?'selected':'';?>>Umum</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Cari
                </button>
            </div>
        </form>
    </div>
</section>

<!-- List -->
<section class="section">
    <div class="container">
        <?php if (empty($items)): ?>
        <div class="text-center py-5">
            <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
            <h5 class="text-dark">Tidak ada pengumuman ditemukan</h5>
            <p class="text-muted">Coba ubah kata kunci atau filter</p>
        </div>
        <?php else: ?>
        
        <div class="row g-4">
            <?php foreach ($items as $p): 
                $priorityClass = $p['priority']==='high' ? 'danger' : ($p['priority']==='medium' ? 'warning' : 'success');
                $priorityText = $p['priority']==='high' ? 'Tinggi' : ($p['priority']==='medium' ? 'Sedang' : 'Rendah');
            ?>
            <div class="col-12">
                <div class="card pengumuman-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-2"><?php echo htmlspecialchars($p['title']); ?></h5>
                                <div class="card-meta mb-3">
                                    <small class="text-muted me-3">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d M Y', strtotime($p['created_at'])); ?>
                                    </small>
                                    <?php if (!empty($p['expiry_date'])): ?>
                                    <small class="text-muted me-3">
                                        <i class="fas fa-clock me-1"></i>
                                        Kadaluarsa: <?php echo date('d M Y', strtotime($p['expiry_date'])); ?>
                                    </small>
                                    <?php endif; ?>
                                    <?php if (!empty($p['target_audience'])): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i>
                                        Target: <?php echo htmlspecialchars($p['target_audience']); ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-end ms-3">
                                <span class="badge badge-<?php echo $priorityClass; ?> me-2"><?php echo $priorityText; ?></span>
                                <span class="badge badge-success">Dipublikasi</span>
                            </div>
                        </div>
                        <div class="card-text">
                            <?php echo nl2br(htmlspecialchars($p['content'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($pagination) && $pagination['total'] > 1): ?>
        <nav aria-label="Pengumuman pagination" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php if (isset($pagination['prev'])): ?>
                <li class="page-item">
                    <a class="page-link" href="pengumuman.php?page=<?php echo $pagination['prev']; ?>&q=<?php echo urlencode($q); ?>&target=<?php echo urlencode($target); ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>

                <?php foreach ($pagination['pages'] as $pageNum): ?>
                <li class="page-item <?php echo $pageNum === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="pengumuman.php?page=<?php echo $pageNum; ?>&q=<?php echo urlencode($q); ?>&target=<?php echo urlencode($target); ?>">
                        <?php echo $pageNum; ?>
                    </a>
                </li>
                <?php endforeach; ?>

                <?php if (isset($pagination['next'])): ?>
                <li class="page-item">
                    <a class="page-link" href="pengumuman.php?page=<?php echo $pagination['next']; ?>&q=<?php echo urlencode($q); ?>&target=<?php echo urlencode($target); ?>">
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