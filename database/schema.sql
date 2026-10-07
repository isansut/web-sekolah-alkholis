-- =====================================================
-- DATABASE SETUP FOR SMA AL-KHOLIS WEBSITE (PHP VERSION)
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS websekolah_alkholis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE websekolah_alkholis;

-- =====================================================
-- USERS TABLE (for admin authentication)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'guru', 'siswa') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password stored as plain text)
INSERT INTO users (username, password, email, role) VALUES 
('admin', 'admin123', 'admin@alkholis.sch.id', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- =====================================================
-- GURU TABLE (must be created before jadwal table)
-- =====================================================
CREATE TABLE IF NOT EXISTS guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nip VARCHAR(20),
    jabatan VARCHAR(100),
    mata_pelajaran VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    image VARCHAR(500),
    bio TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- JADWAL TABLE (for class schedules)
-- =====================================================
CREATE TABLE IF NOT EXISTS jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelas VARCHAR(10) NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    mata_pelajaran VARCHAR(100) NOT NULL,
    guru_id INT,
    ruangan VARCHAR(20),
    semester ENUM('Ganjil', 'Genap') DEFAULT 'Ganjil',
    tahun_ajaran VARCHAR(9) DEFAULT '2024/2025',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE SET NULL
);

-- =====================================================
-- KARYA SISWA TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS karya_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    category ENUM('teknologi', 'seni', 'penelitian', 'multimedia') NOT NULL,
    description TEXT NOT NULL,
    year VARCHAR(4),
    achievement TEXT,
    image VARCHAR(500),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- BERITA TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS berita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    image VARCHAR(500),
    author VARCHAR(100) DEFAULT 'Admin',
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- GALERI TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS galeri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(500) NOT NULL,
    category VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- PENGUMUMAN TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    target_audience VARCHAR(50),
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- SETTINGS TABLE (for website configuration)
-- =====================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- CONTACT MESSAGES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- ACTIVITY LOGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================================
-- SISWA TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    nisn VARCHAR(20) UNIQUE,
    kelas VARCHAR(20),
    jurusan VARCHAR(50),
    email VARCHAR(100),
    phone VARCHAR(20),
    alamat TEXT,
    photo VARCHAR(500),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INDEXES FOR BETTER PERFORMANCE
-- =====================================================

-- Jadwal indexes
CREATE INDEX idx_jadwal_kelas ON jadwal(kelas);
CREATE INDEX idx_jadwal_hari ON jadwal(hari);
CREATE INDEX idx_jadwal_guru_id ON jadwal(guru_id);
CREATE INDEX idx_jadwal_semester ON jadwal(semester);

-- Karya Siswa indexes
CREATE INDEX idx_karya_siswa_status ON karya_siswa(status);
CREATE INDEX idx_karya_siswa_category ON karya_siswa(category);
CREATE INDEX idx_karya_siswa_created_at ON karya_siswa(created_at);

-- Berita indexes
CREATE INDEX idx_berita_status ON berita(status);
CREATE INDEX idx_berita_created_at ON berita(created_at);

-- Guru indexes
CREATE INDEX idx_guru_status ON guru(status);
CREATE INDEX idx_guru_mata_pelajaran ON guru(mata_pelajaran);

-- Galeri indexes
CREATE INDEX idx_galeri_status ON galeri(status);
CREATE INDEX idx_galeri_category ON galeri(category);

-- Pengumuman indexes
CREATE INDEX idx_pengumuman_status ON pengumuman(status);
CREATE INDEX idx_pengumuman_priority ON pengumuman(priority);

-- Contact messages indexes
CREATE INDEX idx_contact_messages_status ON contact_messages(status);
CREATE INDEX idx_contact_messages_created_at ON contact_messages(created_at);

-- Activity logs indexes
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);

-- Siswa indexes
CREATE INDEX idx_siswa_kelas ON siswa(kelas);
CREATE INDEX idx_siswa_jurusan ON siswa(jurusan);
CREATE INDEX idx_siswa_status ON siswa(status); 