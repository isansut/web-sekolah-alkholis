// CRUD Operations JavaScript
class CRUDManager {
    constructor(apiEndpoint) {
        this.apiEndpoint = apiEndpoint;
    }

    // Create new item
    async create(data) {
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert('success', result.message);
                return result;
            } else {
                this.showAlert('danger', result.error);
                return null;
            }
        } catch (error) {
            this.showAlert('danger', 'Terjadi kesalahan: ' + error.message);
            return null;
        }
    }

    // Read all items or single item
    async read(id = null) {
        try {
            const url = id ? `${this.apiEndpoint}?id=${id}` : this.apiEndpoint;
            const response = await fetch(url);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                this.showAlert('danger', result.error);
                return null;
            }
        } catch (error) {
            this.showAlert('danger', 'Terjadi kesalahan: ' + error.message);
            return null;
        }
    }

    // Update item
    async update(data) {
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert('success', result.message);
                return result;
            } else {
                this.showAlert('danger', result.error);
                return null;
            }
        } catch (error) {
            this.showAlert('danger', 'Terjadi kesalahan: ' + error.message);
            return null;
        }
    }

    // Delete item
    async delete(id) {
        if (!confirm('Yakin ingin menghapus item ini?')) {
            return null;
        }

        try {
            const response = await fetch(`${this.apiEndpoint}?id=${id}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert('success', result.message);
                return result;
            } else {
                this.showAlert('danger', result.error);
                return null;
            }
        } catch (error) {
            this.showAlert('danger', 'Terjadi kesalahan: ' + error.message);
            return null;
        }
    }

    // Show alert message
    showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer') || this.createAlertContainer();
        
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        alertContainer.innerHTML = alertHtml;
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }

    // Create alert container if not exists
    createAlertContainer() {
        const container = document.createElement('div');
        container.id = 'alertContainer';
        container.className = 'position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }

    // Load data into table
    async loadTable(tableId, columns, actions = true) {
        const data = await this.read();
        if (!data) return;

        const table = document.getElementById(tableId);
        if (!table) return;

        const tbody = table.querySelector('tbody');
        tbody.innerHTML = '';

        data.forEach(item => {
            const row = document.createElement('tr');
            
            columns.forEach(column => {
                const cell = document.createElement('td');
                if (column.type === 'date') {
                    cell.textContent = new Date(item[column.key]).toLocaleDateString('id-ID');
                } else if (column.type === 'badge') {
                    const badgeClass = this.getBadgeClass(item[column.key]);
                    cell.innerHTML = `<span class="badge ${badgeClass}">${item[column.key]}</span>`;
                } else if (column.type === 'image') {
                    cell.innerHTML = item[column.key] ? 
                        `<img src="${item[column.key]}" alt="${item.title || item.nama}" style="width: 50px; height: 50px; object-fit: cover;">` :
                        '<i class="fas fa-image text-muted"></i>';
                } else {
                    cell.textContent = item[column.key] || '-';
                }
                row.appendChild(cell);
            });

            if (actions) {
                const actionCell = document.createElement('td');
                actionCell.innerHTML = `
                    <button class="btn btn-sm btn-outline-primary" onclick="editItem(${item.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteItem(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                row.appendChild(actionCell);
            }

            tbody.appendChild(row);
        });
    }

    // Get badge class based on status
    getBadgeClass(status) {
        const statusMap = {
            'published': 'bg-success',
            'draft': 'bg-secondary',
            'active': 'bg-success',
            'inactive': 'bg-secondary',
            'pending': 'bg-warning',
            'approved': 'bg-success',
            'rejected': 'bg-danger',
            'unread': 'bg-danger',
            'read': 'bg-success',
            'replied': 'bg-info',
            'high': 'bg-danger',
            'medium': 'bg-warning',
            'low': 'bg-success'
        };
        return statusMap[status] || 'bg-secondary';
    }

    // Load data into form
    async loadForm(formId, id) {
        const data = await this.read(id);
        if (!data) return;

        const form = document.getElementById(formId);
        if (!form) return;

        // Fill form fields
        Object.keys(data).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                if (field.type === 'checkbox') {
                    field.checked = data[key] === '1' || data[key] === true;
                } else {
                    field.value = data[key];
                }
            }
        });

        // Update modal title
        const modal = form.closest('.modal');
        if (modal) {
            const title = modal.querySelector('.modal-title');
            if (title) {
                title.textContent = 'Edit Data';
            }
        }
    }

    // Clear form
    clearForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.reset();
        
        // Update modal title
        const modal = form.closest('.modal');
        if (modal) {
            const title = modal.querySelector('.modal-title');
            if (title) {
                title.textContent = 'Tambah Data Baru';
            }
        }
    }

    // Submit form
    async submitForm(formId, isEdit = false) {
        const form = document.getElementById(formId);
        if (!form) return;

        const formData = new FormData(form);
        const data = {};
        
        formData.forEach((value, key) => {
            data[key] = value;
        });

        let result;
        if (isEdit) {
            result = await this.update(data);
        } else {
            result = await this.create(data);
        }

        if (result) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
            if (modal) {
                modal.hide();
            }

            // Reload table
            this.loadTable('dataTable', this.getTableColumns());
        }
    }

    // Get table columns (to be overridden by specific implementations)
    getTableColumns() {
        return [];
    }
}

// Global CRUD instances
let beritaCRUD, guruCRUD, galeriCRUD, karyaCRUD, pengumumanCRUD, pesanCRUD, jadwalCRUD, settingsCRUD;

// Initialize CRUD managers
document.addEventListener('DOMContentLoaded', function() {
    beritaCRUD = new CRUDManager('api/berita.php');
    guruCRUD = new CRUDManager('api/guru.php');
    galeriCRUD = new CRUDManager('api/galeri.php');
    karyaCRUD = new CRUDManager('api/karya_siswa.php');
    pengumumanCRUD = new CRUDManager('api/pengumuman.php');
    pesanCRUD = new CRUDManager('api/pesan.php');
    jadwalCRUD = new CRUDManager('api/jadwal.php');
    settingsCRUD = new CRUDManager('api/settings.php');
});

// Global functions for buttons
function editItem(id) {
    // This will be overridden by specific page implementations
    console.log('Edit item:', id);
}

function deleteItem(id) {
    // This will be overridden by specific page implementations
    console.log('Delete item:', id);
}

function addItem() {
    // This will be overridden by specific page implementations
    console.log('Add new item');
} 