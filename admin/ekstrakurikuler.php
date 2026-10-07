<?php
session_start();
require_once '../includes/auth_helper.php';

// Check if user is logged in
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Kelola Ekstrakurikuler';

// Get database connection
try {
    $pdo = getDBConnection();
    
    // Get ekstrakurikuler data
    $query = "SELECT e.*, g.nama as nama_pembina, g.image as foto_pembina,
              (SELECT COUNT(*) FROM siswa_ekstrakurikuler se WHERE se.ekstrakurikuler_id = e.id AND se.status = 'active') as jumlah_anggota
              FROM ekstrakurikuler e 
              LEFT JOIN guru g ON e.pembina_id = g.id 
              ORDER BY e.nama";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $ekstrakurikuler_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get guru for pembina selection
    $guru_query = "SELECT id, nama, mata_pelajaran FROM guru WHERE status = 'active' ORDER BY nama";
    $guru_stmt = $pdo->prepare($guru_query);
    $guru_stmt->execute();
    $guru_list = $guru_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Ekstrakurikuler Admin Error: " . $e->getMessage());
    $ekstrakurikuler_data = [];
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
                        <i class="fas fa-users me-2"></i>
                        Kelola Ekstrakurikuler
                    </h2>
                    <p class="text-muted mb-0 text-break">Tambah, edit, dan kelola data ekstrakurikuler</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEkstrakurikulerModal">
                        <i class="fas fa-plus me-2"></i>Tambah Ekstrakurikuler
                    </button>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari ekstrakurikuler...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="fas fa-undo me-2"></i>Reset Filter
                    </button>
                </div>
            </div>

            <!-- Ekstrakurikuler Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Pembina</th>
                            <th>Jadwal</th>
                            <th>Tempat</th>
                            <th>Anggota</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="ekstrakurikulerTableBody">
                        <?php if (!empty($ekstrakurikuler_data)): ?>
                            <?php foreach ($ekstrakurikuler_data as $index => $ekstra): ?>
                            <tr data-id="<?php echo $ekstra['id']; ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($ekstra['nama']); ?></strong>
                                    <?php if (!empty($ekstra['deskripsi'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($ekstra['deskripsi'], 0, 50)); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($ekstra['kategori'])): ?>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($ekstra['kategori']); ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($ekstra['nama_pembina'])): ?>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($ekstra['foto_pembina'])): ?>
                                        <img src="<?php echo $ekstra['foto_pembina']; ?>" 
                                             alt="<?php echo htmlspecialchars($ekstra['nama_pembina']); ?>" 
                                             class="rounded-circle me-2" 
                                             style="width: 200px; height: 150px; object-fit: cover;">
                                        <?php else: ?>
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" 
                                             style="width: 200px; height: 150px;">
                                            <i class="fas fa-user-tie text-muted"></i>
                                        </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($ekstra['nama_pembina']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($ekstra['mata_pelajaran'] ?? ''); ?></small>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-muted">Belum ditentukan</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($ekstra['jadwal'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($ekstra['tempat'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="fas fa-users me-1"></i>
                                        <?php echo $ekstra['jumlah_anggota']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($ekstra['status'] === 'active'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editEkstrakurikuler(<?php echo $ekstra['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteEkstrakurikuler(<?php echo $ekstra['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <h5>Belum Ada Data Ekstrakurikuler</h5>
                                    <p>Silakan tambahkan ekstrakurikuler baru</p>
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

<!-- Add/Edit Ekstrakurikuler Modal -->
<div class="modal fade" id="addEkstrakurikulerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Ekstrakurikuler Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="ekstrakurikulerForm">
                <div class="modal-body">
                    <input type="hidden" id="ekstrakurikulerId" name="id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nama" class="form-label">Nama Ekstrakurikuler <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="kategori" class="form-label">Kategori</label>
                            <select class="form-select" id="kategori" name="kategori">
                                <option value="">Pilih Kategori</option>
                                <option value="Pramuka">Pramuka</option>
                                <option value="PMR">PMR</option>
                                <option value="Rohis">Rohis</option>
                                <option value="Olahraga">Olahraga</option>
                                <option value="Seni">Seni</option>
                                <option value="Teknologi">Teknologi</option>
                                <option value="Akademik">Akademik</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="pembina_id" class="form-label">Pembina</label>
                            <select class="form-select" id="pembina_id" name="pembina_id">
                                <option value="">Pilih Pembina</option>
                                <?php foreach ($guru_list as $guru): ?>
                                <option value="<?php echo $guru['id']; ?>">
                                    <?php echo htmlspecialchars($guru['nama']); ?> 
                                    (<?php echo htmlspecialchars($guru['mata_pelajaran']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="kuota" class="form-label">Kuota Maksimal</label>
                            <input type="number" class="form-control" id="kuota" name="kuota" min="1" value="30">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="jadwal" class="form-label">Jadwal</label>
                            <input type="text" class="form-control" id="jadwal" name="jadwal" placeholder="Contoh: Senin 15:00-17:00">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="tempat" class="form-label">Tempat</label>
                            <input type="text" class="form-control" id="tempat" name="tempat" placeholder="Contoh: Aula Sekolah">
                        </div>
                        
                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" 
                                      placeholder="Deskripsi kegiatan ekstrakurikuler..."></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
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
                <p>Apakah Anda yakin ingin menghapus ekstrakurikuler ini?</p>
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
let currentEkstrakurikulerId = null;

// Search and filter functionality
document.getElementById('searchInput').addEventListener('input', filterEkstrakurikuler);
document.getElementById('statusFilter').addEventListener('change', filterEkstrakurikuler);

function filterEkstrakurikuler() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#ekstrakurikulerTableBody tr');
    
    rows.forEach(row => {
        const nama = row.cells[1].textContent.toLowerCase();
        const status = row.cells[7].textContent.toLowerCase();
        
        let show = true;
        
        if (searchTerm && !nama.includes(searchTerm)) {
            show = false;
        }
        
        if (statusFilter && !status.includes(statusFilter)) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    filterEkstrakurikuler();
}

// Form submission
document.getElementById('ekstrakurikulerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    if (currentEkstrakurikulerId) {
        updateEkstrakurikuler(data);
    } else {
        createEkstrakurikuler(data);
    }
});

// Create ekstrakurikuler
function createEkstrakurikuler(data) {
    fetch('api/ekstrakurikuler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert('success', 'Ekstrakurikuler berhasil ditambahkan!');
            location.reload();
        } else {
            showAlert('danger', 'Gagal menambahkan ekstrakurikuler: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat menambahkan ekstrakurikuler');
    });
}

// Edit ekstrakurikuler
function editEkstrakurikuler(id) {
    currentEkstrakurikulerId = id;
    
    fetch(`api/ekstrakurikuler.php?id=${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const ekstra = result.data;
                
                document.getElementById('ekstrakurikulerId').value = ekstra.id;
                document.getElementById('nama').value = ekstra.nama;
                document.getElementById('kategori').value = ekstra.kategori || '';
                document.getElementById('pembina_id').value = ekstra.pembina_id || '';
                document.getElementById('kuota').value = ekstra.kuota || 30;
                document.getElementById('jadwal').value = ekstra.jadwal || '';
                document.getElementById('tempat').value = ekstra.tempat || '';
                document.getElementById('deskripsi').value = ekstra.deskripsi || '';
                document.getElementById('status').value = ekstra.status;
                
                document.getElementById('modalTitle').textContent = 'Edit Ekstrakurikuler';
                new bootstrap.Modal(document.getElementById('addEkstrakurikulerModal')).show();
            } else {
                showAlert('danger', 'Gagal mengambil data ekstrakurikuler');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Terjadi kesalahan saat mengambil data');
        });
}

// Update ekstrakurikuler
function updateEkstrakurikuler(data) {
    data.id = currentEkstrakurikulerId;
    
    fetch('api/ekstrakurikuler.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert('success', 'Ekstrakurikuler berhasil diperbarui!');
            location.reload();
        } else {
            showAlert('danger', 'Gagal memperbarui ekstrakurikuler: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat memperbarui ekstrakurikuler');
    });
}

// Delete ekstrakurikuler
function deleteEkstrakurikuler(id) {
    currentEkstrakurikulerId = id;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!currentEkstrakurikulerId) return;
    
    fetch(`api/ekstrakurikuler.php?id=${currentEkstrakurikulerId}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert('success', 'Ekstrakurikuler berhasil dihapus!');
            location.reload();
        } else {
            showAlert('danger', 'Gagal menghapus ekstrakurikuler: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat menghapus ekstrakurikuler');
    });
});

// Reset form when modal is closed
document.getElementById('addEkstrakurikulerModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('ekstrakurikulerForm').reset();
    document.getElementById('ekstrakurikulerId').value = '';
    document.getElementById('modalTitle').textContent = 'Tambah Ekstrakurikuler Baru';
    currentEkstrakurikulerId = null;
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
