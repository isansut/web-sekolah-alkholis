<?php
$pageTitle = 'Siswa';
require_once 'includes/header.php';

$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$perPage = 12; 
$offset = ($page-1)*$perPage;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$kelas = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';

$where = "WHERE status = 'active'"; 
$params=[];
if ($kelas!==''){ $where.=" AND kelas = ?"; $params[]=$kelas; }
if ($q!==''){ $where.=" AND (nama LIKE ? OR nisn LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; }

try{
  $pdo = getDBConnection();
  // total
  $c = $pdo->prepare("SELECT COUNT(*) AS total FROM siswa $where"); 
  $c->execute($params); 
  $total = (int)($c->fetch()['total']??0);
  // data
  $s = $pdo->prepare("SELECT * FROM siswa $where ORDER BY kelas, nama LIMIT ? OFFSET ?");
  $s->execute(array_merge($params,[$perPage,$offset]));
  $rows = $s->fetchAll();
  $pagination = getPagination($total,$perPage,$page);
} catch(Exception $e){ 
  $rows=[]; 
  $pagination=[]; 
  $total=0; 
}
?>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
  <div class="container">
    <div class="row justify-content-center text-center">
      <div class="col-lg-8">
        <h1 class="section-title text-white mb-3">Data Siswa</h1>
        <p class="section-subtitle text-white-50">Daftar siswa aktif SMA Al-Kholis</p>
      </div>
    </div>
  </div>
</section>

<!-- Search Section -->
<section class="py-4 bg-light">
  <div class="container">
    <form class="row g-3 align-items-end" method="get" action="siswa.php">
      <div class="col-md-5">
        <label class="form-label fw-semibold text-dark">Pencarian</label>
        <input class="form-control" name="q" value="<?php echo htmlspecialchars($q);?>" placeholder="Cari nama atau NISN">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold text-dark">Kelas</label>
        <input class="form-control" name="kelas" value="<?php echo htmlspecialchars($kelas);?>" placeholder="mis. X IPA 1">
      </div>
      <div class="col-md-3">
        <button class="btn btn-primary w-100" type="submit">
          <i class="fas fa-search me-2"></i>Cari
        </button>
      </div>
    </form>
  </div>
</section>

<!-- Students Section -->
<section class="section">
  <div class="container">
    <?php if (empty($rows)): ?>
      <div class="text-center py-5">
        <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
        <h5 class="text-dark">Data siswa tidak ditemukan</h5>
        <p class="text-muted">Coba ubah filter pencarian Anda</p>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach($rows as $r): ?>
          <div class="col-md-3 col-sm-6">
            <div class="card h-100">
                                  <?php if (!empty($r['photo'])): ?>
                <img src="<?php echo $r['photo'] ? $r['photo'] : 'assets/images/default-siswa.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($r['nama']); ?>" 
                     class="siswa-photo">
              <?php endif; ?>
              <div class="card-body">
                <h6 class="card-title mb-1 text-dark"><?php echo htmlspecialchars($r['nama']); ?></h6>
                <div class="text-muted small mb-1">
                  <i class="fas fa-graduation-cap me-1"></i>
                  <?php echo htmlspecialchars($r['kelas'] ?: '-'); ?>
                </div>
                <div class="text-muted small">
                  <i class="fas fa-id-card me-1"></i>
                  NISN: <?php echo htmlspecialchars($r['nisn'] ?: '-'); ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      
      <!-- Pagination -->
      <?php if (!empty($pagination) && $pagination['total'] > 1): ?>
      <nav aria-label="Siswa pagination" class="mt-5">
        <ul class="pagination justify-content-center">
          <?php if (isset($pagination['prev'])): ?>
            <li class="page-item">
              <a class="page-link" href="siswa.php?page=<?php echo $pagination['prev']; ?>&q=<?php echo urlencode($q); ?>&kelas=<?php echo urlencode($kelas); ?>">
                <i class="fas fa-chevron-left"></i>
              </a>
            </li>
          <?php endif; ?>
          
          <?php foreach ($pagination['pages'] as $pageNum): ?>
            <li class="page-item <?php echo $pageNum===$page?'active':''; ?>">
              <a class="page-link" href="siswa.php?page=<?php echo $pageNum; ?>&q=<?php echo urlencode($q); ?>&kelas=<?php echo urlencode($kelas); ?>">
                <?php echo $pageNum; ?>
              </a>
            </li>
          <?php endforeach; ?>
          
          <?php if (isset($pagination['next'])): ?>
            <li class="page-item">
              <a class="page-link" href="siswa.php?page=<?php echo $pagination['next']; ?>&q=<?php echo urlencode($q); ?>&kelas=<?php echo urlencode($kelas); ?>">
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