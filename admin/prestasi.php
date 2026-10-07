<?php
session_start();
require_once '../includes/auth_helper.php';

// Check if user is logged in
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Kelola Prestasi Siswa';

// Get database connection
try {
    $pdo = getDBConnection();
    
    // Get prestasi data
    $query = "SELECT ps.*, s.nama as nama_siswa, s.kelas, s.photo as foto_siswa,
              g.nama as nama_guru_pembimbing
              FROM prestasi ps 
              JOIN siswa s ON ps.siswa_id = s.id 
              LEFT JOIN guru g ON ps.guru_pembimbing_id = g.id
              WHERE ps.status = 'active' 
              ORDER BY ps.tahun DESC, ps.tingkat DESC, ps.id DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $prestasi_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get siswa for selection
    $siswa_query = "SELECT id, nama, kelas FROM siswa WHERE status = 'active' ORDER BY nama";
    $siswa_stmt = $pdo->prepare($siswa_query);
    $siswa_stmt->execute();
    $siswa_list = $siswa_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get guru for pembimbing selection
    $guru_query = "SELECT id, nama, mata_pelajaran FROM guru WHERE status = 'active' ORDER BY nama";
    $guru_stmt = $pdo->prepare($guru_query);
    $guru_stmt->execute();
    $guru_list = $guru_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Prestasi Admin Error: " . $e->getMessage());
    $prestasi_data = [];
    $siswa_list = [];
    $guru_list = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SMA Al-Kholis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Admin Styles -->
    <link href="includes/admin-styles.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <div class="mb-2 mb-md-0">
                    <h2 class="mb-1 text-break">
                        <i class="fas fa-trophy me-2"></i>
                        Kelola Prestasi Siswa
                    </h2>
                    <p class="text-muted mb-0 text-break">Tambah, edit, dan kelola data prestasi siswa</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPrestasiModal">
                        <i class="fas fa-plus me-2"></i>Tambah Prestasi
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo count($prestasi_data); ?></h4>
                                    <small>Total Prestasi</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-trophy fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo count(array_unique(array_column($prestasi_data, 'kategori'))); ?></h4>
                                    <small>Kategori</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-tags fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo count(array_unique(array_column($prestasi_data, 'tingkat'))); ?></h4>
                                    <small>Tingkat</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-layer-group fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo count(array_unique(array_column($prestasi_data, 'tahun'))); ?></h4>
                                    <small>Tahun</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari prestasi...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="kategoriFilter">
                        <option value="">Semua Kategori</option>
                        <option value="akademik">Akademik</option>
                        <option value="non-akademik">Non-Akademik</option>
                        <option value="olahraga">Olahraga</option>
                        <option value="seni">Seni</option>
                        <option value="teknologi">Teknologi</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="fas fa-undo me-2"></i>Reset Filter
                    </button>
                </div>
            </div>

            <!-- Prestasi Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Prestasi</th>
                            <th>Siswa</th>
                            <th>Kategori</th>
                            <th>Tingkat</th>
                            <th>Peringkat</th>
                            <th>Tahun</th>
                            <th>Guru Pembimbing</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="prestasiTableBody">
                        <?php if (!empty($prestasi_data)): ?>
                            <?php foreach ($prestasi_data as $index => $prestasi): ?>
                            <tr data-id="<?php echo $prestasi['id']; ?>" 
                                data-kategori="<?php echo htmlspecialchars($prestasi['kategori']); ?>"
                                data-tingkat="<?php echo htmlspecialchars($prestasi['tingkat']); ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($prestasi['nama_prestasi']); ?></strong>
                                    <?php if (!empty($prestasi['deskripsi'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($prestasi['deskripsi'], 0, 60)); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
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
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($prestasi['nama_siswa']); ?></div>
                                            <small class="text-muted">Kelas <?php echo htmlspecialchars($prestasi['kelas']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($prestasi['kategori']); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?php echo htmlspecialchars($prestasi['tingkat']); ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($prestasi['peringkat'])): ?>
                                    <span class="badge bg-warning"><?php echo htmlspecialchars($prestasi['peringkat']); ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($prestasi['tahun']); ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($prestasi['nama_guru_pembimbing'])): ?>
                                    <small class="text-muted"><?php echo htmlspecialchars($prestasi['nama_guru_pembimbing']); ?></small>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($prestasi['status'] === 'active'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editPrestasi(<?php echo $prestasi['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deletePrestasi(<?php echo $prestasi['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-trophy fa-3x mb-3"></i>
                                    <h5>Belum Ada Data Prestasi</h5>
                                    <p>Silakan tambahkan prestasi baru</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Prestasi Modal -->
<div class="modal fade" id="addPrestasiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Prestasi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="prestasiForm">
                <div class="modal-body">
                    <input type="hidden" id="prestasiId" name="id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nama_prestasi" class="form-label">Nama Prestasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_prestasi" name="nama_prestasi" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" id="kategori" name="kategori" required>
                                <option value="">Pilih Kategori</option>
                                <option value="akademik">Akademik</option>
                                <option value="non-akademik">Non-Akademik</option>
                                <option value="olahraga">Olahraga</option>
                                <option value="seni">Seni</option>
                                <option value="teknologi">Teknologi</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="tingkat" class="form-label">Tingkat <span class="text-danger">*</span></label>
                            <select class="form-select" id="tingkat" name="tingkat" required>
                                <option value="">Pilih Tingkat</option>
                                <option value="sekolah">Sekolah</option>
                                <option value="kecamatan">Kecamatan</option>
                                <option value="kabupaten">Kabupaten</option>
                                <option value="provinsi">Provinsi</option>
                                <option value="nasional">Nasional</option>
                                <option value="internasional">Internasional</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="peringkat" class="form-label">Peringkat</label>
                            <input type="text" class="form-control" id="peringkat" name="peringkat" 
                                   placeholder="Contoh: Juara 1, Juara 2, dll">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="tahun" class="form-label">Tahun <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="tahun" name="tahun" 
                                   min="2000" max="2030" value="<?php echo date('Y'); ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="siswa_id" class="form-label">Siswa <span class="text-danger">*</span></label>
                            <select class="form-select" id="siswa_id" name="siswa_id" required>
                                <option value="">Pilih Siswa</option>
                                <?php foreach ($siswa_list as $siswa): ?>
                                <option value="<?php echo $siswa['id']; ?>">
                                    <?php echo htmlspecialchars($siswa['nama']); ?> 
                                    (Kelas <?php echo htmlspecialchars($siswa['kelas']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="guru_pembimbing_id" class="form-label">Guru Pembimbing</label>
                            <select class="form-select" id="guru_pembimbing_id" name="guru_pembimbing_id">
                                <option value="">Pilih Guru Pembimbing</option>
                                <?php foreach ($guru_list as $guru): ?>
                                <option value="<?php echo $guru['id']; ?>">
                                    <?php echo htmlspecialchars($guru['nama']); ?> 
                                    (<?php echo htmlspecialchars($guru['mata_pelajaran']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" 
                                      placeholder="Deskripsi prestasi..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus prestasi ini?</p>
                <p class="text-danger"><strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-2"></i>Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentPrestasiId = null;

// Search and filter functionality
document.getElementById('searchInput').addEventListener('input', filterPrestasi);
document.getElementById('kategoriFilter').addEventListener('change', filterPrestasi);

function filterPrestasi() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const kategoriFilter = document.getElementById('kategoriFilter').value;
    const rows = document.querySelectorAll('#prestasiTableBody tr');
    
    rows.forEach(row => {
        const nama = row.cells[1].textContent.toLowerCase();
        const kategori = row.dataset.kategori;
        
        let show = true;
        
        if (searchTerm && !nama.includes(searchTerm)) {
            show = false;
        }
        
        if (kategoriFilter && kategori !== kategoriFilter) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('kategoriFilter').value = '';
    filterPrestasi();
}

// Form submission
document.getElementById('prestasiForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    if (currentPrestasiId) {
        updatePrestasi(data);
    } else {
        createPrestasi(data);
    }
});

// Create prestasi
function createPrestasi(data) {
    fetch('api/prestasi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert('success', 'Prestasi berhasil ditambahkan!');
            location.reload();
        } else {
            showAlert('danger', 'Gagal menambahkan prestasi: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat menambahkan prestasi');
    });
}

// Edit prestasi
function editPrestasi(id) {
    currentPrestasiId = id;
    
    fetch(`api/prestasi.php?id=${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const prestasi = result.data;
                
                document.getElementById('prestasiId').value = prestasi.id;
                document.getElementById('nama_prestasi').value = prestasi.nama_prestasi;
                document.getElementById('kategori').value = prestasi.kategori;
                document.getElementById('tingkat').value = prestasi.tingkat;
                document.getElementById('peringkat').value = prestasi.peringkat || '';
                document.getElementById('tahun').value = prestasi.tahun;
                document.getElementById('siswa_id').value = prestasi.siswa_id;
                document.getElementById('guru_pembimbing_id').value = prestasi.guru_pembimbing_id || '';
                document.getElementById('status').value = prestasi.status;
                document.getElementById('deskripsi').value = prestasi.deskripsi || '';
                
                document.getElementById('modalTitle').textContent = 'Edit Prestasi';
                new bootstrap.Modal(document.getElementById('addPrestasiModal')).show();
            } else {
                showAlert('danger', 'Gagal mengambil data prestasi');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Terjadi kesalahan saat mengambil data');
        });
}

// Update prestasi
function updatePrestasi(data) {
    data.id = currentPrestasiId;
    
    fetch('api/prestasi.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert('success', 'Prestasi berhasil diperbarui!');
            location.reload();
        } else {
            showAlert('danger', 'Gagal memperbarui prestasi: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat memperbarui prestasi');
    });
}

// Delete prestasi
function deletePrestasi(id) {
    currentPrestasiId = id;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!currentPrestasiId) return;
    
    fetch(`api/prestasi.php?id=${currentPrestasiId}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert('success', 'Prestasi berhasil dihapus!');
            location.reload();
        } else {
            showAlert('danger', 'Gagal menghapus prestasi: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat menghapus prestasi');
    });
});

// Reset form when modal is closed
document.getElementById('addPrestasiModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('prestasiForm').reset();
    document.getElementById('prestasiId').value = '';
    document.getElementById('modalTitle').textContent = 'Tambah Prestasi Baru';
    currentPrestasiId = null;
});

// Show alert function
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.main-content').insertBefore(alertDiv, document.querySelector('.main-content').firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>

</body>
</html>
