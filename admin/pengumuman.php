<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengumuman - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Admin Styles -->
    <link href="includes/admin-styles.css" rel="stylesheet">
    <style>
        .pengumuman-item {
            transition: transform 0.2s;
        }
        .pengumuman-item:hover {
            transform: translateY(-2px);
        }
        .priority-high {
            border-left: 4px solid #dc3545;
        }
        .priority-medium {
            border-left: 4px solid #ffc107;
        }
        .priority-low {
            border-left: 4px solid #28a745;
        }
        .expired {
            opacity: 0.6;
        }
        
        /* Loading spinner */
        .loading-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #1e3a8a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Card styling */
        .pengumuman-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .pengumuman-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        /* Button group responsive */
        .btn-group .btn {
            padding: 0.375rem 0.5rem;
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .btn-group .btn {
                padding: 0.25rem 0.375rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Include Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                        <div class="mb-2 mb-md-0">
                            <h2 class="text-break"><i class="fas fa-bullhorn me-2"></i>Kelola Pengumuman</h2>
                        </div>
                        <div>
                            <button class="btn btn-primary" onclick="addPengumuman()">
                                <i class="fas fa-plus me-2"></i>Tambah Pengumuman
                            </button>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 col-sm-12">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari pengumuman...">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <select class="form-select" id="priorityFilter">
                                <option value="">Semua Prioritas</option>
                                <option value="high">Tinggi</option>
                                <option value="medium">Sedang</option>
                                <option value="low">Rendah</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <select class="form-select" id="statusFilter">
                                <option value="">Semua Status</option>
                                <option value="draft">Draft</option>
                                <option value="published">Dipublikasi</option>
                                <option value="archived">Diarsipkan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <div id="alertContainer"></div>

                    <!-- Pengumuman List -->
                    <div class="row g-3" id="pengumumanContainer">
                        <!-- Pengumuman items will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengumuman Modal -->
    <div class="modal fade" id="pengumumanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Pengumuman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="pengumumanForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Judul Pengumuman *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Isi Pengumuman *</label>
                            <textarea class="form-control" id="content" name="content" rows="6" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Prioritas</label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="low">Rendah</option>
                                        <option value="medium" selected>Sedang</option>
                                        <option value="high">Tinggi</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft">Draft</option>
                                        <option value="published" selected>Dipublikasi</option>
                                        <option value="archived">Diarsipkan</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="target_audience" class="form-label">Target Audience</label>
                                    <select class="form-select" id="target_audience" name="target_audience">
                                        <option value="">Semua</option>
                                        <option value="siswa">Siswa</option>
                                        <option value="guru">Guru</option>
                                        <option value="ortu">Orang Tua</option>
                                        <option value="umum">Umum</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label">Tanggal Kadaluarsa</label>
                                    <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                                    <div class="form-text">Kosongkan jika tidak ada kadaluarsa (opsional)</div>
                                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/crud.js"></script>
    <script>
        // Pengumuman CRUD specific functions (native fetch)
        let currentEditId = null;

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing pengumuman...');
            
            // Check if required elements exist
            const requiredElements = {
                'searchInput': document.getElementById('searchInput'),
                'priorityFilter': document.getElementById('priorityFilter'),
                'statusFilter': document.getElementById('statusFilter'),
                'pengumumanContainer': document.getElementById('pengumumanContainer'),
                'alertContainer': document.getElementById('alertContainer')
            };
            
            console.log('Required elements check:', requiredElements);
            
            // Check for missing elements
            const missingElements = Object.keys(requiredElements).filter(key => !requiredElements[key]);
            if (missingElements.length > 0) {
                console.warn('Missing elements:', missingElements);
            }
            
            // Initialize event listeners
            if (requiredElements.searchInput) {
                requiredElements.searchInput.addEventListener('input', filterPengumuman);
            }
            if (requiredElements.priorityFilter) {
                requiredElements.priorityFilter.addEventListener('change', filterPengumuman);
            }
            if (requiredElements.statusFilter) {
                requiredElements.statusFilter.addEventListener('change', filterPengumuman);
            }
            
            // Load initial data
            console.log('Loading initial pengumuman list...');
            loadPengumumanList();
        });

        async function loadPengumumanList() {
            try {
                const container = document.getElementById('pengumumanContainer');
                
                // Show loading state
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <div class="loading-spinner mb-3"></div>
                        <p class="text-muted">Memuat data pengumuman...</p>
                    </div>
                `;
                
                const res = await fetch('api/pengumuman.php');
                const json = await res.json();
                
                if (!json.success) {
                    container.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h5 class="text-warning">Gagal memuat data</h5>
                            <p class="text-muted">${json.error || 'Terjadi kesalahan saat memuat data'}</p>
                            <button class="btn btn-primary" onclick="loadPengumumanList()">
                                <i class="fas fa-redo me-2"></i>Coba Lagi
                            </button>
                        </div>
                    `;
                    return;
                }
                
                const data = json.data || [];
                
                if (data.length === 0) {
                    container.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada pengumuman</h5>
                            <p class="text-muted">Mulai dengan menambahkan pengumuman pertama</p>
                            <button class="btn btn-primary" onclick="addPengumuman()">
                                <i class="fas fa-plus me-2"></i>Tambah Pengumuman
                            </button>
                        </div>
                    `;
                    return;
                }
                
                // Clear container and render data
                container.innerHTML = '';
                data.forEach(pengumuman => {
                    const pengumumanItem = document.createElement('div');
                    pengumumanItem.className = 'col-12 mb-3 pengumuman-item';
                    pengumumanItem.dataset.priority = pengumuman.priority || '';
                    pengumumanItem.dataset.status = pengumuman.status || '';
                    pengumumanItem.dataset.search = `${(pengumuman.title||'')} ${(pengumuman.content||'')}`.toLowerCase();

                    const priorityClass = pengumuman.priority === 'high' ? 'priority-high' : 
                                        pengumuman.priority === 'medium' ? 'priority-medium' : 'priority-low';
                    const priorityText = pengumuman.priority === 'high' ? 'Tinggi' : 
                                       pengumuman.priority === 'medium' ? 'Sedang' : 'Rendah';
                    const priorityBadgeClass = pengumuman.priority === 'high' ? 'danger' : 
                                             pengumuman.priority === 'medium' ? 'warning' : 'success';
                    
                    const statusBadgeClass = pengumuman.status === 'published' ? 'success' : 
                                           pengumuman.status === 'draft' ? 'secondary' : 'info';
                    const statusText = pengumuman.status === 'published' ? 'Dipublikasi' : 
                                     pengumuman.status === 'draft' ? 'Draft' : 'Diarsipkan';
                    
                    const isExpired = pengumuman.expiry_date && new Date(pengumuman.expiry_date) < new Date();
                    const expiredClass = isExpired ? 'expired' : '';
                    
                    pengumumanItem.innerHTML = `
                        <div class="card ${priorityClass} ${expiredClass} pengumuman-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="card-title mb-1">${pengumuman.title}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>${new Date(pengumuman.created_at).toLocaleDateString('id-ID')}
                                            ${pengumuman.expiry_date ? `<br><i class="fas fa-clock me-1"></i>Kadaluarsa: ${new Date(pengumuman.expiry_date).toLocaleDateString('id-ID')}` : ''}
                                            ${pengumuman.target_audience ? `<br><i class="fas fa-users me-1"></i>Target: ${pengumuman.target_audience}` : ''}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-${priorityBadgeClass} me-1">${priorityText}</span>
                                        <span class="badge bg-${statusBadgeClass}">${statusText}</span>
                                        ${isExpired ? '<span class="badge bg-danger ms-1">Kadaluarsa</span>' : ''}
                                    </div>
                                </div>
                                <p class="card-text">${pengumuman.content}</p>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" onclick="editPengumuman(${pengumuman.id})">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deletePengumuman(${pengumuman.id})">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(pengumumanItem);
                });
                
                console.log(`Loaded ${data.length} pengumuman items`);
                
            } catch (e) {
                console.error('Error loading pengumuman list:', e);
                const container = document.getElementById('pengumumanContainer');
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">Error</h5>
                        <p class="text-muted">${e.message || 'Terjadi kesalahan yang tidak diketahui'}</p>
                        <button class="btn btn-primary" onclick="loadPengumumanList()">
                            <i class="fas fa-redo me-2"></i>Coba Lagi
                        </button>
                    </div>
                `;
            }
        }

        function filterPengumuman() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const priorityFilter = document.getElementById('priorityFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const items = document.querySelectorAll('.pengumuman-item');
            items.forEach(item => {
                const matchesSearch = !searchTerm || (item.dataset.search || '').includes(searchTerm);
                const matchesPriority = !priorityFilter || (item.dataset.priority || '') === priorityFilter;
                const matchesStatus = !statusFilter || (item.dataset.status || '') === statusFilter;
                item.style.display = (matchesSearch && matchesPriority && matchesStatus) ? 'block' : 'none';
            });
        }

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const div = document.createElement('div');
            div.className = `alert alert-${type} alert-dismissible fade show`;
            div.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            alertContainer.appendChild(div);
            setTimeout(() => div.remove(), 4000);
        }

        function addPengumuman() {
            currentEditId = null;
            document.getElementById('pengumumanForm').reset();
            document.getElementById('modalTitle').textContent = 'Tambah Pengumuman';
            const modal = new bootstrap.Modal(document.getElementById('pengumumanModal'));
            modal.show();
        }

        async function editPengumuman(id) {
            currentEditId = id;
            try {
                const res = await fetch(`api/pengumuman.php?id=${id}`);
                const json = await res.json();
                if (!json.success || !json.data) {
                    showAlert(json.error || 'Pengumuman tidak ditemukan', 'danger');
                    return;
                }
                const d = json.data;
                const f = document.getElementById('pengumumanForm');
                ['title','content','priority','status','target_audience','expiry_date'].forEach(name => {
                    const el = f.querySelector(`[name="${name}"]`);
                    if (el && d[name] !== undefined) el.value = d[name] ?? '';
                });
                document.getElementById('modalTitle').textContent = 'Edit Pengumuman';
            } catch (e) {
                showAlert('Error: ' + e.message, 'danger');
                return;
            }
            const modal = new bootstrap.Modal(document.getElementById('pengumumanModal'));
            modal.show();
        }

        async function deletePengumuman(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')) return;
            try {
                const res = await fetch(`api/pengumuman.php?id=${id}`, { method: 'DELETE' });
                const json = await res.json();
                if (json.success) {
                    loadPengumumanList();
                    showAlert('Pengumuman berhasil dihapus', 'success');
                } else {
                    showAlert(json.error || 'Gagal menghapus pengumuman', 'danger');
                }
            } catch (e) {
                showAlert('Error: ' + e.message, 'danger');
            }
        }

        document.getElementById('pengumumanForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                if (currentEditId) {
                    const fd = new FormData(this);
                    fd.set('id', currentEditId);
                    const params = new URLSearchParams();
                    fd.forEach((v,k)=>params.append(k,v));
                    const res = await fetch('api/pengumuman.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: params.toString()
                    });
                    const json = await res.json();
                    if (!json.success) {
                        showAlert(json.error || 'Gagal mengupdate pengumuman', 'danger');
                        return;
                    }
                } else {
                    const fd = new FormData(this);
                    const res = await fetch('api/pengumuman.php', { method: 'POST', body: fd });
                    const json = await res.json();
                    if (!json.success) {
                        showAlert(json.error || 'Gagal menambah pengumuman', 'danger');
                        return;
                    }
                }
                const modal = bootstrap.Modal.getInstance(document.getElementById('pengumumanModal'));
                if (modal) modal.hide();
                loadPengumumanList();
                showAlert('Perubahan tersimpan', 'success');
            } catch (e) {
                showAlert('Error: ' + e.message, 'danger');
            }
        });

        // Override global functions
        window.editItem = editPengumuman;
        window.deleteItem = deletePengumuman;
        window.addItem = addPengumuman;
    </script>
</body>
</html> 