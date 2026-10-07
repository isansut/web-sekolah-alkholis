-- =====================================================
-- COMPLETE DATABASE SCHEMA FOR SMA AL-KHOLIS WEBSITE
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
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- GURU TABLE (Teachers)
-- =====================================================
CREATE TABLE IF NOT EXISTS guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nip VARCHAR(20) UNIQUE,
    jabatan VARCHAR(100) DEFAULT 'Guru',
    mata_pelajaran VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    alamat TEXT,
    image VARCHAR(500),
    bio TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- SISWA TABLE (Students)
-- =====================================================
CREATE TABLE IF NOT EXISTS siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    nisn VARCHAR(20) UNIQUE,
    nis VARCHAR(20),
    kelas VARCHAR(20),
    jurusan VARCHAR(50),
    tempat_lahir VARCHAR(100),
    tanggal_lahir DATE,
    jenis_kelamin ENUM('L', 'P'),
    agama VARCHAR(20),
    email VARCHAR(100),
    phone VARCHAR(20),
    alamat TEXT,
    photo VARCHAR(500),
    nama_ortu VARCHAR(100),
    phone_ortu VARCHAR(20),
    status ENUM('active', 'inactive', 'lulus') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- JADWAL TABLE (Class Schedules)
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
-- EKSTRAKURIKULER TABLE (Extracurricular Activities)
-- =====================================================
CREATE TABLE IF NOT EXISTS ekstrakurikuler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    pembina_id INT,
    kategori VARCHAR(100),
    jadwal VARCHAR(255),
    tempat VARCHAR(255),
    kuota INT DEFAULT 30,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pembina_id) REFERENCES guru(id) ON DELETE SET NULL
);

-- =====================================================
-- SISWA EKSTRAKURIKULER TABLE (Junction table)
-- =====================================================
CREATE TABLE IF NOT EXISTS siswa_ekstrakurikuler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT,
    ekstrakurikuler_id INT,
    tanggal_daftar DATE,
    status ENUM('active', 'inactive', 'graduated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (ekstrakurikuler_id) REFERENCES ekstrakurikuler(id) ON DELETE CASCADE
);

-- =====================================================
-- BERITA TABLE (News)
-- =====================================================
CREATE TABLE IF NOT EXISTS berita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    image VARCHAR(500),
    author VARCHAR(100) DEFAULT 'Admin',
    category VARCHAR(50) DEFAULT 'Umum',
    tags VARCHAR(255),
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    featured BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- GALERI TABLE (Gallery)
-- =====================================================
CREATE TABLE IF NOT EXISTS galeri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(500) NOT NULL,
    category VARCHAR(50),
    album VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- KARYA SISWA TABLE (Student Works)
-- =====================================================
CREATE TABLE IF NOT EXISTS karya_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    category ENUM('teknologi', 'seni', 'penelitian', 'multimedia', 'literasi', 'olahraga') NOT NULL,
    description TEXT NOT NULL,
    year VARCHAR(4),
    achievement TEXT,
    image VARCHAR(500),
    file_url VARCHAR(500),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- PENGUMUMAN TABLE (Announcements)
-- =====================================================
CREATE TABLE IF NOT EXISTS pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    target_audience VARCHAR(50),
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- PRESTASI TABLE (Achievements)
-- =====================================================
CREATE TABLE IF NOT EXISTS prestasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_prestasi VARCHAR(255) NOT NULL,
    kategori ENUM('akademik', 'non-akademik', 'olahraga', 'seni', 'teknologi') NOT NULL,
    tingkat ENUM('sekolah', 'kecamatan', 'kabupaten', 'provinsi', 'nasional', 'internasional') NOT NULL,
    peringkat VARCHAR(50),
    tahun VARCHAR(4),
    deskripsi TEXT,
    siswa_id INT,
    guru_pembimbing_id INT,
    image VARCHAR(500),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE SET NULL,
    FOREIGN KEY (guru_pembimbing_id) REFERENCES guru(id) ON DELETE SET NULL
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
    status ENUM('unread', 'read', 'replied', 'spam') DEFAULT 'unread',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- SETTINGS TABLE (Website Configuration)
-- =====================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    category VARCHAR(50) DEFAULT 'general',
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
    table_name VARCHAR(50),
    record_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================================
-- INDEXES FOR BETTER PERFORMANCE
-- =====================================================

-- Users indexes
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);

-- Guru indexes
CREATE INDEX idx_guru_nama ON guru(nama);
CREATE INDEX idx_guru_nip ON guru(nip);
CREATE INDEX idx_guru_mata_pelajaran ON guru(mata_pelajaran);
CREATE INDEX idx_guru_status ON guru(status);

-- Siswa indexes
CREATE INDEX idx_siswa_nama ON siswa(nama);
CREATE INDEX idx_siswa_nisn ON siswa(nisn);
CREATE INDEX idx_siswa_kelas ON siswa(kelas);
CREATE INDEX idx_siswa_jurusan ON siswa(jurusan);
CREATE INDEX idx_siswa_status ON siswa(status);

-- Jadwal indexes
CREATE INDEX idx_jadwal_kelas ON jadwal(kelas);
CREATE INDEX idx_jadwal_hari ON jadwal(hari);
CREATE INDEX idx_jadwal_guru_id ON jadwal(guru_id);
CREATE INDEX idx_jadwal_semester ON jadwal(semester);
CREATE INDEX idx_jadwal_status ON jadwal(status);

-- Ekstrakurikuler indexes
CREATE INDEX idx_ekstrakurikuler_nama ON ekstrakurikuler(nama);
CREATE INDEX idx_ekstrakurikuler_kategori ON ekstrakurikuler(kategori);
CREATE INDEX idx_ekstrakurikuler_pembina_id ON ekstrakurikuler(pembina_id);
CREATE INDEX idx_ekstrakurikuler_status ON ekstrakurikuler(status);

-- Siswa Ekstrakurikuler indexes
CREATE INDEX idx_siswa_ekstrakurikuler_siswa_id ON siswa_ekstrakurikuler(siswa_id);
CREATE INDEX idx_siswa_ekstrakurikuler_ekstrakurikuler_id ON siswa_ekstrakurikuler(ekstrakurikuler_id);
CREATE INDEX idx_siswa_ekstrakurikuler_status ON siswa_ekstrakurikuler(status);

-- Berita indexes
CREATE INDEX idx_berita_title ON berita(title);
CREATE INDEX idx_berita_category ON berita(category);
CREATE INDEX idx_berita_status ON berita(status);
CREATE INDEX idx_berita_featured ON berita(featured);
CREATE INDEX idx_berita_created_at ON berita(created_at);

-- Galeri indexes
CREATE INDEX idx_galeri_title ON galeri(title);
CREATE INDEX idx_galeri_category ON galeri(category);
CREATE INDEX idx_galeri_album ON galeri(album);
CREATE INDEX idx_galeri_status ON galeri(status);

-- Karya Siswa indexes
CREATE INDEX idx_karya_siswa_title ON karya_siswa(title);
CREATE INDEX idx_karya_siswa_category ON karya_siswa(category);
CREATE INDEX idx_karya_siswa_author ON karya_siswa(author);
CREATE INDEX idx_karya_siswa_status ON karya_siswa(status);
CREATE INDEX idx_karya_siswa_year ON karya_siswa(year);

-- Pengumuman indexes
CREATE INDEX idx_pengumuman_title ON pengumuman(title);
CREATE INDEX idx_pengumuman_priority ON pengumuman(priority);
CREATE INDEX idx_pengumuman_status ON pengumuman(status);
CREATE INDEX idx_pengumuman_target_audience ON pengumuman(target_audience);

-- Prestasi indexes
CREATE INDEX idx_prestasi_nama ON prestasi(nama_prestasi);
CREATE INDEX idx_prestasi_kategori ON prestasi(kategori);
CREATE INDEX idx_prestasi_tingkat ON prestasi(tingkat);
CREATE INDEX idx_prestasi_tahun ON prestasi(tahun);
CREATE INDEX idx_prestasi_siswa_id ON prestasi(siswa_id);

-- Contact messages indexes
CREATE INDEX idx_contact_messages_email ON contact_messages(email);
CREATE INDEX idx_contact_messages_status ON contact_messages(status);
CREATE INDEX idx_contact_messages_created_at ON contact_messages(created_at);

-- Settings indexes
CREATE INDEX idx_settings_key ON settings(setting_key);
CREATE INDEX idx_settings_category ON settings(category);

-- Activity logs indexes
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_action ON activity_logs(action);
CREATE INDEX idx_activity_logs_table_name ON activity_logs(table_name);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);
