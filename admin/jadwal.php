<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Kelola Jadwal Pelajaran';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SMA Al-Kholis</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Admin Styles -->
    <link href="includes/admin-styles.css" rel="stylesheet">
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .table th {
            background: #1e3a8a;
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .jadwal-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .jadwal-card:hover {
            transform: translateY(-5px);
        }
        
        .jadwal-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 15px;
            border-radius: 15px 15px 0 0;
        }
        
        .btn-action {
            margin: 0 2px;
        }
        
        .time-badge {
            background: #10b981;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .day-badge {
            background: #f59e0b;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
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
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                    <div class="mb-2 mb-md-0">
                        <h2 class="mb-1 text-break">Kelola Jadwal Pelajaran</h2>
                        <p class="text-muted mb-0 text-break">Tambah, edit, dan kelola jadwal pelajaran sekolah</p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="addJadwal()">
                            <i class="fas fa-plus me-2"></i>Tambah Jadwal
                        </button>
                    </div>
                </div>
                
                <!-- Filter -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-sm-6">
                        <select class="form-select" id="filterKelas">
                            <option value="">Semua Kelas</option>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
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
                    <div class="col-md-3 col-sm-6">
                        <select class="form-select" id="filterSemester">
                            <option value="">Semua Semester</option>
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <input type="text" class="form-control" id="searchJadwal" placeholder="Cari jadwal...">
                    </div>
                </div>
                
                <!-- Jadwal Cards -->
                <div class="row g-3" id="jadwalContainer">
                    <!-- Data will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Jadwal Modal -->
    <div class="modal fade" id="jadwalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jadwal Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="jadwalForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="jadwalId">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kelas</label>
                                    <select class="form-select" name="kelas" required>
                                        <option value="">Pilih Kelas</option>
                                        <option value="X">Kelas X</option>
                                        <option value="XI">Kelas XI</option>
                                        <option value="XII">Kelas XII</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Hari</label>
                                    <select class="form-select" name="hari" required>
                                        <option value="">Pilih Hari</option>
                                        <option value="Senin">Senin</option>
                                        <option value="Selasa">Selasa</option>
                                        <option value="Rabu">Rabu</option>
                                        <option value="Kamis">Kamis</option>
                                        <option value="Jumat">Jumat</option>
                                        <option value="Sabtu">Sabtu</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jam Mulai</label>
                                    <input type="time" class="form-control" name="jam_mulai" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jam Selesai</label>
                                    <input type="time" class="form-control" name="jam_selesai" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Mata Pelajaran</label>
                                    <input type="text" class="form-control" name="mata_pelajaran" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Guru</label>
                                    <select class="form-select" name="guru_id" id="guruSelect">
                                        <option value="">Pilih Guru (Opsional)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ruangan</label>
                                    <input type="text" class="form-control" name="ruangan" placeholder="Contoh: Lab 1, Ruang 101">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Semester</label>
                                    <select class="form-select" name="semester" required>
                                        <option value="Ganjil">Ganjil</option>
                                        <option value="Genap">Genap</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tahun Ajaran</label>
                                    <input type="text" class="form-control" name="tahun_ajaran" value="2024/2025" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="active">Aktif</option>
                                        <option value="inactive">Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- CRUD JS -->
    <script src="js/crud.js"></script>
    
    <script>
        // Jadwal CRUD specific functions
        let currentEditId = null;
        
        // Load jadwal cards
        async function loadJadwalCards() {
            try {
                const response = await fetch('api/jadwal.php');
                const result = await response.json();
                
                if (result.success) {
                    displayJadwalCards(result.data);
                } else {
                    showAlert(result.error || 'Gagal memuat data jadwal', 'danger');
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'danger');
            }
        }
        
        // Display jadwal cards
        function displayJadwalCards(jadwalData) {
            const container = document.getElementById('jadwalContainer');
            container.innerHTML = '';
            
            if (jadwalData.length === 0) {
                container.innerHTML = `
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada jadwal</h5>
                            <p class="text-muted">Belum ada jadwal pelajaran yang ditambahkan.</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            // Group by day
            const groupedByDay = {};
            jadwalData.forEach(jadwal => {
                if (!groupedByDay[jadwal.hari]) {
                    groupedByDay[jadwal.hari] = [];
                }
                groupedByDay[jadwal.hari].push(jadwal);
            });
            
            // Create cards for each day
            const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            days.forEach(day => {
                if (groupedByDay[day]) {
                    const dayCard = document.createElement('div');
                    dayCard.className = 'col-12 mb-4';
                    dayCard.innerHTML = `
                        <div class="jadwal-card">
                            <div class="jadwal-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    ${day}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row" id="jadwal-${day}">
                                    <!-- Jadwal items will be loaded here -->
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(dayCard);
                    
                    // Sort by time and add jadwal items
                    const dayContainer = document.getElementById(`jadwal-${day}`);
                    groupedByDay[day].sort((a, b) => a.jam_mulai.localeCompare(b.jam_mulai));
                    
                    groupedByDay[day].forEach(jadwal => {
                        const jadwalItem = document.createElement('div');
                        jadwalItem.className = 'col-md-6 col-lg-4 mb-3';
                        jadwalItem.innerHTML = `
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="time-badge">${jadwal.jam_mulai} - ${jadwal.jam_selesai}</span>
                                        <span class="badge bg-${jadwal.status === 'active' ? 'success' : 'secondary'}">${jadwal.status}</span>
                                    </div>
                                    <h6 class="card-title mb-1">${jadwal.mata_pelajaran}</h6>
                                    <p class="card-text text-muted mb-2">
                                        <small>
                                            <i class="fas fa-users me-1"></i>${jadwal.kelas}
                                            ${jadwal.ruangan ? `<br><i class="fas fa-map-marker-alt me-1"></i>${jadwal.ruangan}` : ''}
                                            ${jadwal.guru_nama ? `<br><i class="fas fa-user-tie me-1"></i>${jadwal.guru_nama}` : ''}
                                        </small>
                                    </p>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" onclick="editJadwal(${jadwal.id})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteJadwal(${jadwal.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        dayContainer.appendChild(jadwalItem);
                    });
                }
            });
        }
        
        // Load guru options
        async function loadGuruOptions() {
            try {
                const response = await fetch('api/guru.php');
                const result = await response.json();
                
                if (result.success) {
                    const guruSelect = document.getElementById('guruSelect');
                    guruSelect.innerHTML = '<option value="">Pilih Guru (Opsional)</option>';
                    
                    result.data.forEach(guru => {
                        const option = document.createElement('option');
                        option.value = guru.id;
                        option.textContent = guru.nama + ' - ' + guru.mata_pelajaran;
                        guruSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading guru options:', error);
            }
        }
        
        // Add new jadwal
        function addJadwal() {
            currentEditId = null;
            document.getElementById('jadwalForm').reset();
            document.getElementById('jadwalId').value = '';
            document.querySelector('#jadwalModal .modal-title').textContent = 'Tambah Jadwal Baru';
            
            const modal = new bootstrap.Modal(document.getElementById('jadwalModal'));
            modal.show();
        }
        
        // Edit jadwal
        async function editJadwal(id) {
            try {
                const response = await fetch(`api/jadwal.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    currentEditId = id;
                    fillJadwalForm(result.data);
                    document.querySelector('#jadwalModal .modal-title').textContent = 'Edit Jadwal';
                    
                    const modal = new bootstrap.Modal(document.getElementById('jadwalModal'));
                    modal.show();
                } else {
                    showAlert(result.error || 'Gagal memuat data jadwal', 'danger');
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'danger');
            }
        }
        
        // Fill form with jadwal data
        function fillJadwalForm(jadwal) {
            const form = document.getElementById('jadwalForm');
            form.id.value = jadwal.id;
            form.kelas.value = jadwal.kelas;
            form.hari.value = jadwal.hari;
            form.jam_mulai.value = jadwal.jam_mulai;
            form.jam_selesai.value = jadwal.jam_selesai;
            form.mata_pelajaran.value = jadwal.mata_pelajaran;
            form.guru_id.value = jadwal.guru_id || '';
            form.ruangan.value = jadwal.ruangan || '';
            form.semester.value = jadwal.semester;
            form.tahun_ajaran.value = jadwal.tahun_ajaran;
            form.status.value = jadwal.status;
        }
        
        // Delete jadwal
        async function deleteJadwal(id) {
            if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
                try {
                    const response = await fetch(`api/jadwal.php?id=${id}`, {
                        method: 'DELETE'
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        loadJadwalCards();
                        showAlert('Jadwal berhasil dihapus', 'success');
                    } else {
                        showAlert(result.error || 'Gagal menghapus jadwal', 'danger');
                    }
                } catch (error) {
                    showAlert('Error: ' + error.message, 'danger');
                }
            }
        }
        
        // Submit jadwal form
        document.getElementById('jadwalForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Convert FormData to object for validation
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            // Validate required fields
            if (!data.kelas || !data.hari || !data.jam_mulai || !data.jam_selesai || !data.mata_pelajaran) {
                showAlert('Semua field wajib diisi', 'danger');
                return;
            }
            
            try {
                if (currentEditId) {
                    formData.append('id', currentEditId);
                    
                    // Convert FormData to URLSearchParams for PUT request
                    const params = new URLSearchParams();
                    formData.forEach((value, key) => {
                        params.append(key, value);
                    });
                    
                    // Send as PUT for update
                    const response = await fetch('api/jadwal.php', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: params.toString()
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Close modal using Bootstrap
                        const modal = bootstrap.Modal.getInstance(document.getElementById('jadwalModal'));
                        if (modal) {
                            modal.hide();
                        }
                        loadJadwalCards();
                        showAlert('Jadwal berhasil diupdate', 'success');
                    } else {
                        showAlert(result.error || 'Gagal mengupdate jadwal', 'danger');
                    }
                } else {
                    // Send as FormData for create
                    const response = await fetch('api/jadwal.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Close modal using Bootstrap
                        const modal = bootstrap.Modal.getInstance(document.getElementById('jadwalModal'));
                        if (modal) {
                            modal.hide();
                        }
                        loadJadwalCards();
                        showAlert('Jadwal berhasil ditambahkan', 'success');
                    } else {
                        showAlert(result.error || 'Gagal menambahkan jadwal', 'danger');
                    }
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'danger');
            }
        });
        
        // Show alert
        function showAlert(message, type) {
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
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadJadwalCards();
            loadGuruOptions();
            
            // Reset form when modal is hidden
            document.getElementById('jadwalModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('jadwalForm').reset();
                currentEditId = null;
            });
        });
    </script>
</body>
</html> 