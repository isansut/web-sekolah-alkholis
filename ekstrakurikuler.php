<?php
$pageTitle = 'Ekstrakurikuler';
require_once 'includes/header.php';

// Get database connection
try {
    $pdo = getDBConnection();
    
    // Get ekstrakurikuler data
    $query = "SELECT e.*, g.nama as nama_pembina, g.image as foto_pembina,
              (SELECT COUNT(*) FROM siswa_ekstrakurikuler se WHERE se.ekstrakurikuler_id = e.id AND se.status = 'active') as jumlah_anggota
              FROM ekstrakurikuler e 
              LEFT JOIN guru g ON e.pembina_id = g.id 
              WHERE e.status = 'active' 
              ORDER BY e.nama";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $ekstrakurikuler_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get categories for filter
    $categories = array_unique(array_column($ekstrakurikuler_data, 'kategori'));
    $categories = array_filter($categories); // Remove empty values
    
} catch (Exception $e) {
    error_log("Ekstrakurikuler Error: " . $e->getMessage());
    $ekstrakurikuler_data = [];
    $categories = [];
}
?>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="section-title text-white mb-3">Ekstrakurikuler</h1>
                <p class="section-subtitle text-white-50">
                    Berbagai kegiatan ekstrakurikuler untuk mengembangkan bakat dan minat siswa
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Filter Section -->
<?php if (!empty($categories)): ?>
<section class="section bg-light py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="filter-container text-center">
                    <div class="btn-group" role="group" aria-label="Filter Ekstrakurikuler">
                        <button type="button" class="btn btn-outline-primary active" data-filter="all">
                            Semua
                        </button>
                        <?php foreach ($categories as $category): ?>
                        <button type="button" class="btn btn-outline-primary" data-filter="<?php echo htmlspecialchars($category); ?>">
                            <?php echo htmlspecialchars($category); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Ekstrakurikuler Section -->
<section class="section py-5">
    <div class="container">
        <?php if (!empty($ekstrakurikuler_data)): ?>
        <div class="row g-4">
            <?php foreach ($ekstrakurikuler_data as $ekstra): ?>
            <div class="col-lg-6 col-md-6" data-category="<?php echo htmlspecialchars($ekstra['kategori'] ?? ''); ?>">
                <div class="card ekstra-card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start mb-3">
                            <div class="ekstra-icon me-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($ekstra['nama']); ?></h5>
                                <?php if (!empty($ekstra['kategori'])): ?>
                                <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($ekstra['kategori']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($ekstra['deskripsi'])): ?>
                        <p class="card-text text-muted mb-3"><?php echo htmlspecialchars(truncateText($ekstra['deskripsi'], 120)); ?></p>
                        <?php endif; ?>
                        
                        <div class="row g-2 mb-3">
                            <?php if (!empty($ekstra['jadwal'])): ?>
                            <div class="col-6">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo htmlspecialchars($ekstra['jadwal']); ?>
                                </small>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($ekstra['tempat'])): ?>
                            <div class="col-6">
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($ekstra['tempat']); ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                                           <?php if (!empty($ekstra['foto_pembina'])): ?>
                           <img src="<?php echo $ekstra['foto_pembina']; ?>" 
                                alt="<?php echo htmlspecialchars($ekstra['nama_pembina'] ?? 'Pembina'); ?>" 
                                class="rounded-circle me-2" 
                                style="width: 200px; height: 150px; object-fit: cover;">
                           <?php else: ?>
                           <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" 
                                style="width: 200px; height: 150px;">
                               <i class="fas fa-user-tie text-muted"></i>
                           </div>
                           <?php endif; ?>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($ekstra['nama_pembina'] ?? 'Pembina'); ?>
                                </small>
                            </div>
                            
                            <div class="text-end">
                                <span class="badge bg-success">
                                    <i class="fas fa-users me-1"></i>
                                    <?php echo $ekstra['jumlah_anggota']; ?> Anggota
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <!-- No Data Message -->
        <div class="text-center">
            <div class="alert alert-info">
                <i class="fas fa-info-circle fa-2x mb-3"></i>
                <h4>Belum Ada Ekstrakurikuler</h4>
                <p>Belum ada data ekstrakurikuler yang tersedia. Silakan kunjungi kembali nanti.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* Custom styles for ekstrakurikuler page */
.ekstra-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.ekstra-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.ekstra-icon {
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

.filter-container .btn-group .btn {
    border-radius: 0.5rem;
    margin: 0 0.25rem;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.filter-container .btn.active {
    background: linear-gradient(135deg, #1e40af, #06b6d4);
    border-color: #1e40af;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .ekstra-card {
        margin-bottom: 1rem;
    }
    
    .filter-container .btn-group {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .filter-container .btn {
        margin: 0;
    }
}
</style>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('[data-filter]');
    const ekstraItems = document.querySelectorAll('[data-category]');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter items
            ekstraItems.forEach(item => {
                const category = item.getAttribute('data-category');
                if (filter === 'all' || category === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 