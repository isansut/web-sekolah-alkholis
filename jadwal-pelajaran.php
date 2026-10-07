<?php
require_once 'config/database.php';

// Get database connection
$pdo = getDBConnection();

// Get jadwal data with better filtering
$query = "SELECT j.*, g.nama as nama_guru 
          FROM jadwal j 
          LEFT JOIN guru g ON j.guru_id = g.id 
          WHERE j.status = 'active' 
          ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai";

$stmt = $pdo->prepare($query);
$stmt->execute();
$jadwal_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by hari
$jadwal_by_hari = [];
foreach ($jadwal_data as $jadwal) {
    $hari = $jadwal['hari'];
    if (!isset($jadwal_by_hari[$hari])) {
        $jadwal_by_hari[$hari] = [];
    }
    $jadwal_by_hari[$hari][] = $jadwal;
}

// Get unique values for filters
$kelas_list = array_unique(array_column($jadwal_data, 'kelas'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran - SMA Al-Kholis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #7c3aed;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --light-bg: #f8fafc;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        body {
            background-color: var(--light-bg);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }

        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
        }

        .filter-row {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-select {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn-filter {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .btn-filter:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
            box-shadow: var(--hover-shadow);
            color: white;
        }

        .btn-reset {
            background: #6b7280;
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .btn-reset:hover {
            background: #4b5563;
            color: white;
        }

        .jadwal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .jadwal-day {
            opacity: 1;
            transform: scale(1);
            transition: all 0.3s ease;
        }

        .jadwal-day.hidden {
            opacity: 0;
            transform: scale(0.95);
            display: none;
        }

        .jadwal-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            height: 100%;
        }

        .jadwal-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
        }

        .hari-header {
            text-align: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 8px;
        }

        .hari-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .jadwal-item {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .jadwal-item:hover {
            background: #f1f5f9;
            border-color: var(--primary-color);
        }

        .jadwal-item.hidden {
            display: none !important;
        }

        .jadwal-item.filtered-out {
            opacity: 0.5;
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .jadwal-time {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }

        .jadwal-mapel {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }

        .jadwal-guru {
            color: #6b7280;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }

        .jadwal-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .badge-kelas {
            background: var(--success-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-ruangan {
            background: var(--warning-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        .filter-active {
            background: var(--success-color) !important;
            color: white !important;
        }

        .filter-active::after {
            content: ' ✓';
            font-weight: bold;
        }

        .filter-section .form-select:focus {
            border-color: var(--success-color);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .filter-section .form-select:not([value=""]) {
            border-color: var(--success-color);
            background-color: #f0fdf4;
        }

        .filter-count {
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        @media (max-width: 768px) {
            .jadwal-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-row {
                flex-direction: column;
            }
            
            .filter-group {
                min-width: 100%;
            }
            
            .page-header {
                padding: 2rem 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container text-center">
            <h1 class="display-5 fw-bold mb-2">
                <i class="fas fa-calendar-alt me-2"></i>
                Jadwal Pelajaran
            </h1>
            <p class="lead mb-0">Jadwal pembelajaran SMA Al-Kholis</p>
        </div>
    </section>

    <main class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <h5 class="mb-3 text-center text-muted">
                <i class="fas fa-filter me-2"></i>Filter Jadwal
            </h5>
            
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Kelas</label>
                    <select class="form-select" id="filterKelas">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($kelas_list as $kelas): ?>
                            <option value="<?php echo htmlspecialchars($kelas); ?>">Kelas <?php echo htmlspecialchars($kelas); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Hari</label>
                    <select class="form-select" id="filterHari">
                        <option value="">Semua Hari</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button class="btn btn-filter w-100" onclick="applyFilter()">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                </div>
                
                <div class="filter-group">
                    <button class="btn btn-reset w-100" onclick="resetFilter()">
                        <i class="fas fa-undo me-2"></i>Reset
                    </button>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    Menampilkan <span id="filterCount"><?php echo count($jadwal_data); ?></span> jadwal
                </small>
            </div>
        </div>

        <!-- Jadwal Content -->
        <?php if (empty($jadwal_by_hari)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h4>Belum Ada Jadwal</h4>
                <p>Jadwal pelajaran belum tersedia. Silakan hubungi admin untuk mengisi jadwal.</p>
            </div>
        <?php else: ?>
            <div class="jadwal-grid">
                <?php foreach ($jadwal_by_hari as $hari => $jadwal_list): ?>
                    <?php 
                    // Get unique values for this day
                    $kelas_hari = array_unique(array_column($jadwal_list, 'kelas'));
                    ?>
                    <div class="jadwal-day" 
                         data-kelas="<?php echo htmlspecialchars(implode(',', $kelas_hari)); ?>">
                        <div class="jadwal-card">
                            <div class="hari-header">
                                <h3 class="hari-title">
                                    <?php
                                    $hari_icons = [
                                        'Senin' => 'fas fa-sun',
                                        'Selasa' => 'fas fa-cloud-sun',
                                        'Rabu' => 'fas fa-cloud',
                                        'Kamis' => 'fas fa-cloud-rain',
                                        'Jumat' => 'fas fa-sun',
                                        'Sabtu' => 'fas fa-star'
                                    ];
                                    $icon = $hari_icons[$hari] ?? 'fas fa-calendar-day';
                                    ?>
                                    <i class="<?php echo $icon; ?> me-2"></i>
                                    <?php echo $hari; ?>
                                </h3>
                            </div>
                            
                            <?php foreach ($jadwal_list as $jadwal): ?>
                                <div class="jadwal-item" 
                                     data-kelas="<?php echo htmlspecialchars($jadwal['kelas']); ?>">
                                    <div class="jadwal-time">
                                        <i class="fas fa-clock me-2"></i>
                                        <?php echo date('H:i', strtotime($jadwal['jam_mulai'])); ?> - 
                                        <?php echo date('H:i', strtotime($jadwal['jam_selesai'])); ?>
                                    </div>
                                    
                                    <div class="jadwal-mapel">
                                        <i class="fas fa-book me-2"></i>
                                        <?php echo htmlspecialchars($jadwal['mata_pelajaran']); ?>
                                    </div>
                                    
                                    <div class="jadwal-guru">
                                        <i class="fas fa-user-tie me-2"></i>
                                        <?php echo htmlspecialchars($jadwal['nama_guru'] ?? 'Guru belum ditentukan'); ?>
                                    </div>
                                    
                                    <div class="jadwal-meta">
                                        <span class="badge-kelas">
                                            <i class="fas fa-users me-1"></i>
                                            Kelas <?php echo htmlspecialchars($jadwal['kelas']); ?>
                                        </span>
                                        
                                        <?php if (!empty($jadwal['ruangan'])): ?>
                                            <span class="badge-ruangan">
                                                <i class="fas fa-door-open me-1"></i>
                                                <?php echo htmlspecialchars($jadwal['ruangan']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentFilters = {
            kelas: '',
            hari: ''
        };

        function applyFilter() {
            const kelas = document.getElementById('filterKelas').value;
            const hari = document.getElementById('filterHari').value;
            
            currentFilters = { kelas, hari };
            
            console.log('Applying filters:', currentFilters); // Debug log
            
            const jadwalDays = document.querySelectorAll('.jadwal-day');
            let visibleCount = 0;
            
            jadwalDays.forEach(day => {
                // Get all jadwal items in this day
                const jadwalItems = day.querySelectorAll('.jadwal-item');
                let hasVisibleItems = false;
                
                jadwalItems.forEach(item => {
                    const itemKelas = item.dataset.kelas || '';
                    const itemHari = day.querySelector('.hari-title').textContent.trim();
                    
                    let itemVisible = true;
                    
                    // Apply filters
                    if (kelas && itemKelas !== kelas) {
                        itemVisible = false;
                    }
                    if (hari && itemHari !== hari) {
                        itemVisible = false;
                    }
                    
                    // Show/hide individual jadwal item
                    if (itemVisible) {
                        item.style.display = 'block';
                        hasVisibleItems = true;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                // Show/hide the entire day card based on whether it has visible items
                if (hasVisibleItems) {
                    day.classList.remove('hidden');
                    visibleCount++;
                } else {
                    day.classList.add('hidden');
                }
            });
            
            console.log('Visible count:', visibleCount); // Debug log
            
            // Update filter count
            document.getElementById('filterCount').textContent = visibleCount;
            
            // Update filter button states
            updateFilterButtonStates();
            
            // Show feedback
            showFilterFeedback();
        }
        
        function resetFilter() {
            document.getElementById('filterKelas').value = '';
            document.getElementById('filterHari').value = '';
            
            currentFilters = { kelas: '', hari: '' };
            
            const jadwalDays = document.querySelectorAll('.jadwal-day');
            jadwalDays.forEach(day => {
                day.classList.remove('hidden');
                
                // Show all jadwal items
                const jadwalItems = day.querySelectorAll('.jadwal-item');
                jadwalItems.forEach(item => {
                    item.style.display = 'block';
                });
            });
            
            // Update filter count
            document.getElementById('filterCount').textContent = jadwalDays.length;
            
            // Update filter button states
            updateFilterButtonStates();
            
            // Show feedback
            showFilterFeedback('Filter telah direset');
        }
        
        function updateFilterButtonStates() {
            const filterBtn = document.querySelector('.btn-filter');
            const resetBtn = document.querySelector('.btn-reset');
            
            const hasActiveFilters = Object.values(currentFilters).some(value => value !== '');
            
            if (hasActiveFilters) {
                filterBtn.classList.add('filter-active');
                resetBtn.style.display = 'block';
                
                // Update filter button text
                filterBtn.innerHTML = '<i class="fas fa-check me-2"></i>Filter Aktif';
                
                // Show active filters info
                showActiveFiltersInfo();
            } else {
                filterBtn.classList.remove('filter-active');
                resetBtn.style.display = 'none';
                
                // Reset filter button text
                filterBtn.innerHTML = '<i class="fas fa-search me-2"></i>Filter';
                
                // Hide active filters info
                hideActiveFiltersInfo();
            }
        }
        
        function showActiveFiltersInfo() {
            let activeFiltersInfo = document.getElementById('activeFiltersInfo');
            if (!activeFiltersInfo) {
                activeFiltersInfo = document.createElement('div');
                activeFiltersInfo.id = 'activeFiltersInfo';
                activeFiltersInfo.className = 'alert alert-info mt-3';
                document.querySelector('.filter-section').appendChild(activeFiltersInfo);
            }
            
            const activeFilters = [];
            if (currentFilters.kelas) activeFilters.push(`Kelas: ${currentFilters.kelas}`);
            if (currentFilters.hari) activeFilters.push(`Hari: ${currentFilters.hari}`);
            
            activeFiltersInfo.innerHTML = `
                <i class="fas fa-info-circle me-2"></i>
                <strong>Filter Aktif:</strong> ${activeFilters.join(' | ')}
            `;
        }
        
        function hideActiveFiltersInfo() {
            const activeFiltersInfo = document.getElementById('activeFiltersInfo');
            if (activeFiltersInfo) {
                activeFiltersInfo.remove();
            }
        }
        
        function showFilterFeedback(message = 'Filter diterapkan') {
            // Create or update feedback element
            let feedback = document.getElementById('filterFeedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.id = 'filterFeedback';
                feedback.className = 'alert alert-success alert-dismissible fade show mt-3';
                feedback.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.filter-section').appendChild(feedback);
            } else {
                feedback.querySelector('.alert').textContent = message;
                feedback.classList.remove('fade');
                feedback.offsetHeight; // Trigger reflow
                feedback.classList.add('fade');
            }
            
            // Auto-hide after 3 seconds
            setTimeout(() => {
                if (feedback) {
                    feedback.remove();
                }
            }, 3000);
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateFilterButtonStates();
            
            // Add change event listeners to filters
            document.getElementById('filterKelas').addEventListener('change', function() {
                console.log('Kelas changed to:', this.value); // Debug log
                applyFilter();
            });
            
            document.getElementById('filterHari').addEventListener('change', function() {
                console.log('Hari changed to:', this.value); // Debug log
                applyFilter();
            });
            
            // Debug: Log all jadwal data
            console.log('All jadwal days:', document.querySelectorAll('.jadwal-day'));
            document.querySelectorAll('.jadwal-day').forEach((day, index) => {
                console.log(`Day ${index + 1}:`, {
                    hari: day.querySelector('.hari-title').textContent.trim(),
                    jadwalItems: day.querySelectorAll('.jadwal-item').length
                });
            });
        });
    </script>
</body>
</html> 