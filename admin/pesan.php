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
    <title>Kelola Pesan - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Admin Styles -->
    <link href="includes/admin-styles.css" rel="stylesheet">
    <style>
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .pesan-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .pesan-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .pesan-card.unread {
            border-left-color: #007bff;
            background-color: #f8f9ff;
        }
        
        .priority-high {
            border-left-color: #dc3545;
        }
        
        .priority-medium {
            border-left-color: #ffc107;
        }
        
        .priority-low {
            border-left-color: #28a745;
        }
        
        .message-preview {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .pesan-item {
            transition: all 0.3s ease;
        }
        
        .pesan-item:hover {
            transform: scale(1.01);
        }
        
        .btn-group .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        
        @media (max-width: 768px) {
            .btn-group .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
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
                            <h2 class="text-break"><i class="fas fa-envelope me-2"></i>Kelola Pesan</h2>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-success" onclick="markAllRead()">
                                <i class="fas fa-check-double me-2"></i>Tandai Semua Dibaca
                            </button>
                            <button class="btn btn-danger" onclick="deleteAllRead()">
                                <i class="fas fa-trash me-2"></i>Hapus Yang Dibaca
                            </button>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 col-sm-12">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari pesan...">
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
                                <option value="unread">Belum Dibaca</option>
                                <option value="read">Sudah Dibaca</option>
                                <option value="replied">Sudah Dibalas</option>
                            </select>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <div id="alertContainer"></div>

                    <!-- Pesan List -->
                    <div class="row g-3" id="pesanContainer">
                        <!-- Pesan items will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pesan Detail Modal -->
    <div class="modal fade" id="pesanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Detail Pesan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="pesanDetail">
                    <!-- Pesan detail will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-success" id="markReadBtn" onclick="markAsRead()">
                        <i class="fas fa-check me-2"></i>Tandai Dibaca
                    </button>
                    <button type="button" class="btn btn-danger" id="deleteBtn" onclick="deletePesan()">
                        <i class="fas fa-trash me-2"></i>Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/pesan.js"></script>
</body>
</html> 