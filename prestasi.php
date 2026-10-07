<?php
$pageTitle = 'Prestasi Siswa';
require_once 'includes/header.php';

// Get database connection
try {
    $pdo = getDBConnection();
    
    // Get prestasi data with pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 12;
    $offset = ($page - 1) * $limit;
    
    // Get total count
    $countQuery = "SELECT COUNT(*) FROM prestasi ps 
                   JOIN siswa s ON ps.siswa_id = s.id 
                   WHERE ps.status = 'active'";
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);
    
    // Get prestasi data
    $query = "SELECT ps.*, s.nama as nama_siswa, s.kelas, s.photo as foto_siswa
              FROM prestasi ps 
              JOIN siswa s ON ps.siswa_id = s.id 
              WHERE ps.status = 'active' 
              ORDER BY ps.tahun DESC, ps.tingkat DESC, ps.id DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $prestasi_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get filter options
    $kategori_list = $pdo->query("SELECT DISTINCT kategori FROM prestasi WHERE status = 'active' ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);
    $tingkat_list = $pdo->query("SELECT DISTINCT tingkat FROM prestasi WHERE status = 'active' ORDER BY tingkat")->fetchAll(PDO::FETCH_COLUMN);
    $tahun_list = $pdo->query("SELECT DISTINCT tahun FROM prestasi WHERE status = 'active' ORDER BY tahun DESC")->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    error_log("Prestasi Error: " . $e->getMessage());
    $prestasi_data = [];
    $kategori_list = [];
    $tingkat_list = [];
    $tahun_list = [];
    $totalPages = 0;
    $page = 1;
}
?>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="section-title text-white mb-3">Prestasi Siswa</h1>
                <p class="section-subtitle text-white-50">
                    Prestasi membanggakan siswa SMA Al-Kholis di berbagai bidang
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Filter Section -->
<?php if (!empty($kategori_list) || !empty($tingkat_list) || !empty($tahun_list)): ?>
<section class="section bg-light py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="filter-container text-center">
                    <div class="row g-3 justify-content-center">
                        <?php if (!empty($kategori_list)): ?>
                        <div class="col-md-3">
                            <select class="form-select" id="filterKategori">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($kategori_list as $kategori): ?>
                                <option value="<?php echo htmlspecialchars($kategori); ?>"><?php echo htmlspecialchars($kategori); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($tingkat_list)): ?>
                        <div class="col-md-3">
                            <select class="form-select" id="filterTingkat">
                                <option value="">Semua Tingkat</option>
                                <?php foreach ($tingkat_list as $tingkat): ?>
                                <option value="<?php echo htmlspecialchars($tingkat); ?>"><?php echo htmlspecialchars($tingkat); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($tahun_list)): ?>
                        <div class="col-md-3">
                            <select class="form-select" id="filterTahun">
                                <option value="">Semua Tahun</option>
                                <?php foreach ($tahun_list as $tahun): ?>
                                <option value="<?php echo htmlspecialchars($tahun); ?>"><?php echo htmlspecialchars($tahun); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" onclick="applyFilter()">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Prestasi Section -->
<section class="section py-5">
    <div class="container">
        <?php if (!empty($prestasi_data)): ?>
        <div class="row g-4" id="prestasiContainer">
            <?php foreach ($prestasi_data as $prestasi): ?>
            <div class="col-lg-4 col-md-6 prestasi-item" 
                 data-kategori="<?php echo htmlspecialchars($prestasi['kategori']); ?>"
                 data-tingkat="<?php echo htmlspecialchars($prestasi['tingkat']); ?>"
                 data-tahun="<?php echo htmlspecialchars($prestasi['tahun']); ?>">
                <div class="card prestasi-card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start mb-3">
                            <div class="prestasi-icon me-3">
                                <?php
                                $icons = [
                                    'akademik' => 'fas fa-graduation-cap',
                                    'non-akademik' => 'fas fa-star',
                                    'olahraga' => 'fas fa-running',
                                    'seni' => 'fas fa-palette',
                                    'teknologi' => 'fas fa-laptop-code'
                                ];
                                $icon = $icons[$prestasi['kategori']] ?? 'fas fa-trophy';
                                ?>
                                <i class="<?php echo $icon; ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($prestasi['nama_prestasi']); ?></h6>
                                <div class="mb-2">
                                    <span class="badge bg-primary me-1"><?php echo htmlspecialchars($prestasi['kategori']); ?></span>
                                    <span class="badge bg-success"><?php echo htmlspecialchars($prestasi['tingkat']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($prestasi['deskripsi'])): ?>
                        <p class="card-text text-muted mb-3"><?php echo htmlspecialchars(truncateText($prestasi['deskripsi'], 100)); ?></p>
                        <?php endif; ?>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo htmlspecialchars($prestasi['tahun']); ?>
                                </small>
                            </div>
                            <?php if (!empty($prestasi['peringkat'])): ?>
                            <div class="col-6">
                                <small class="text-muted">
                                    <i class="fas fa-medal me-1"></i>
                                    <?php echo htmlspecialchars($prestasi['peringkat']); ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                                           <?php if (!empty($prestasi['foto_siswa'])): ?>
                           <img src="<?php echo $prestasi['foto_siswa']; ?>" 
                                alt="<?php echo htmlspecialchars($prestasi['nama_siswa']); ?>" 
                                class="rounded-circle me-2" 
                                style="width: 200px; height: 150px; object-fit: cover;">
                           <?php else: ?>
                           <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" 
                                style="width: 200px; height: 150px;">
                               <i class="fas fa-user text-muted"></i>
                           </div>
                           <?php endif; ?>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($prestasi['nama_siswa']); ?>
                                    <br><span class="text-primary"><?php echo htmlspecialchars($prestasi['kelas']); ?></span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center mt-5">
            <nav aria-label="Prestasi pagination">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <!-- No Data Message -->
        <div class="text-center">
            <div class="alert alert-info">
                <i class="fas fa-info-circle fa-2x mb-3"></i>
                <h4>Belum Ada Prestasi</h4>
                <p>Belum ada data prestasi yang tersedia. Silakan kunjungi kembali nanti.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* Custom styles for prestasi page */
.prestasi-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.prestasi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.prestasi-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #1e40af, #06b6d4);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.filter-container .form-select {
    border-radius: 0.5rem;
    border: 2px solid #e2e8f0;
    padding: 0.5rem 1rem;
}

.filter-container .form-select:focus {
    border-color: #1e40af;
    box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
}

.badge {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
}

.pagination .page-link {
    color: #1e40af;
    border-color: #e2e8f0;
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #1e40af, #06b6d4);
    border-color: #1e40af;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .prestasi-card {
        margin-bottom: 1rem;
    }
    
    .filter-container .row {
        gap: 1rem !important;
    }
}
</style>

<script>
// Filter functionality
function applyFilter() {
    const kategori = document.getElementById('filterKategori')?.value || '';
    const tingkat = document.getElementById('filterTahun')?.value || '';
    const tahun = document.getElementById('filterTahun')?.value || '';
    
    const prestasiItems = document.querySelectorAll('.prestasi-item');
    
    prestasiItems.forEach(item => {
        let show = true;
        
        if (kategori && item.dataset.kategori !== kategori) {
            show = false;
        }
        if (tingkat && item.dataset.tingkat !== tingkat) {
            show = false;
        }
        if (tahun && item.dataset.tahun !== tahun) {
            show = false;
        }
        
        item.style.display = show ? 'block' : 'none';
    });
}

// Auto-filter on change
document.addEventListener('DOMContentLoaded', function() {
    const filterSelects = document.querySelectorAll('.form-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', applyFilter);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 