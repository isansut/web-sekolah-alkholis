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
    <title>Pengaturan Website - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin: 0.25rem 0;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .main-content {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .setting-item {
            transition: transform 0.2s;
        }
        .setting-item:hover {
            transform: translateY(-2px);
        }
        .setting-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .icon-info { background: linear-gradient(45deg, #17a2b8, #20c997); }
        .icon-contact { background: linear-gradient(45deg, #28a745, #20c997); }
        .icon-social { background: linear-gradient(45deg, #6f42c1, #e83e8c); }
        .icon-system { background: linear-gradient(45deg, #fd7e14, #ffc107); }
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
                            <h2 class="text-break"><i class="fas fa-cog me-2"></i>Pengaturan Website</h2>
                        </div>
                        <div>
                            <button class="btn btn-primary" onclick="addSetting()">
                                <i class="fas fa-plus me-2"></i>Tambah Pengaturan
                            </button>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <div id="alertContainer"></div>

                    <!-- Settings Categories -->
                    <div class="row g-3" id="settingsContainer">
                        <!-- Settings items will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Setting Modal -->
    <div class="modal fade" id="settingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Pengaturan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="settingForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="setting_key" class="form-label">Kunci Pengaturan *</label>
                            <input type="text" class="form-control" id="setting_key" name="setting_key" required>
                            <div class="form-text">Contoh: school_name, contact_email, social_facebook</div>
                        </div>
                        <div class="mb-3">
                            <label for="setting_value" class="form-label">Nilai Pengaturan *</label>
                            <textarea class="form-control" id="setting_value" name="setting_value" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
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
        // Settings CRUD specific functions
        let currentEditId = null;
        let settingsCRUD;

        // Initialize CRUD manager
        document.addEventListener('DOMContentLoaded', function() {
            settingsCRUD = new CRUDManager('api/settings.php');
            loadSettings();
        });

        // Load settings
        async function loadSettings() {
            const data = await settingsCRUD.read();
            if (!data) return;
            
            const container = document.getElementById('settingsContainer');
            container.innerHTML = '';
            
            // Group settings by category
            const categories = {
                'info': { title: 'Informasi Sekolah', icon: 'fas fa-info-circle', color: 'icon-info' },
                'contact': { title: 'Kontak', icon: 'fas fa-phone', color: 'icon-contact' },
                'social': { title: 'Social Media', icon: 'fas fa-share-alt', color: 'icon-social' },
                'system': { title: 'Sistem', icon: 'fas fa-cogs', color: 'icon-system' }
            };
            
            // Categorize settings
            const categorized = {};
            data.forEach(setting => {
                const key = setting.setting_key;
                let category = 'system'; // default
                
                if (key.includes('school') || key.includes('name') || key.includes('address')) {
                    category = 'info';
                } else if (key.includes('contact') || key.includes('email') || key.includes('phone')) {
                    category = 'contact';
                } else if (key.includes('social') || key.includes('facebook') || key.includes('instagram')) {
                    category = 'social';
                }
                
                if (!categorized[category]) {
                    categorized[category] = [];
                }
                categorized[category].push(setting);
            });
            
            // Create category sections
            Object.keys(categories).forEach(category => {
                if (categorized[category] && categorized[category].length > 0) {
                    const categorySection = document.createElement('div');
                    categorySection.className = 'col-12 mb-4';
                    categorySection.innerHTML = `
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <div class="setting-icon ${categories[category].color} me-3">
                                        <i class="${categories[category].icon}"></i>
                                    </div>
                                    <h5 class="mb-0">${categories[category].title}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row" id="settings-${category}">
                                    <!-- Settings for this category -->
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(categorySection);
                    
                    // Add settings to category
                    const categoryContainer = document.getElementById(`settings-${category}`);
                    categorized[category].forEach(setting => {
                        const settingItem = document.createElement('div');
                        settingItem.className = 'col-md-6 col-lg-4 mb-3 setting-item';
                        settingItem.innerHTML = `
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title">${setting.setting_key}</h6>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary" onclick="editSetting(${setting.id})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteSetting(${setting.id})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small">${setting.setting_value}</p>
                                    ${setting.description ? `<small class="text-muted">${setting.description}</small>` : ''}
                                </div>
                            </div>
                        `;
                        categoryContainer.appendChild(settingItem);
                    });
                }
            });
            
            // If no settings, show empty state
            if (Object.keys(categorized).length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-cog fa-4x text-muted mb-3"></i>
                        <h4>Belum ada pengaturan</h4>
                        <p class="text-muted">Tambahkan pengaturan website untuk mengkonfigurasi informasi sekolah.</p>
                        <button class="btn btn-primary" onclick="addSetting()">
                            <i class="fas fa-plus me-2"></i>Tambah Pengaturan Pertama
                        </button>
                    </div>
                `;
            }
        }

        // Add new setting
        function addSetting() {
            currentEditId = null;
            settingsCRUD.clearForm('settingForm');
            document.getElementById('modalTitle').textContent = 'Tambah Pengaturan';
            const modal = new bootstrap.Modal(document.getElementById('settingModal'));
            modal.show();
        }

        // Edit setting
        async function editSetting(id) {
            currentEditId = id;
            await settingsCRUD.loadForm('settingForm', id);
            document.getElementById('modalTitle').textContent = 'Edit Pengaturan';
            const modal = new bootstrap.Modal(document.getElementById('settingModal'));
            modal.show();
        }

        // Delete setting
        async function deleteSetting(id) {
            if (confirm('Apakah Anda yakin ingin menghapus pengaturan ini?')) {
                const result = await settingsCRUD.delete(id);
                if (result) {
                    loadSettings();
                }
            }
        }

        // Submit setting form
        document.getElementById('settingForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            let result;
            if (currentEditId) {
                data.id = currentEditId;
                result = await settingsCRUD.update(data);
            } else {
                result = await settingsCRUD.create(data);
            }
            
            if (result) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('settingModal'));
                modal.hide();
                loadSettings();
            }
        });

        // Override global functions
        window.editItem = editSetting;
        window.deleteItem = deleteSetting;
        window.addItem = addSetting;
    </script>
</body>
</html> 