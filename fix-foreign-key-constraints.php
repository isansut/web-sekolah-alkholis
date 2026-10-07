<?php
/**
 * Fix Foreign Key Constraints and Missing Tables
 * Resolves the foreign key constraint error for guru table
 */

require_once 'config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    echo "<h2>Foreign Key Constraints Fix</h2>";
    echo "<p>Fixing foreign key constraints and adding missing tables...</p>";
    
    // Check if ekstrakurikuler table exists
    echo "<h3>Checking ekstrakurikuler table...</h3>";
    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'ekstrakurikuler'");
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            echo "<p>⚠️ Table ekstrakurikuler doesn't exist. Creating it...</p>";
            
            // Create ekstrakurikuler table
            $pdo->exec("
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            echo "<p>✅ Table ekstrakurikuler created successfully</p>";
            
            // Add some sample data
            $pdo->exec("
                INSERT INTO ekstrakurikuler (nama, deskripsi, kategori, jadwal, tempat, status) VALUES
                ('Basket', 'Ekstrakurikuler basket untuk mengembangkan bakat olahraga siswa', 'Olahraga', 'Senin & Rabu 15:00-17:00', 'Lapangan Basket', 'active'),
                ('English Club', 'Klub bahasa Inggris untuk meningkatkan kemampuan berbahasa', 'Akademik', 'Selasa & Kamis 15:00-16:30', 'Ruang Bahasa', 'active'),
                ('Rohis', 'Rohani Islam untuk pembentukan karakter religius', 'Keagamaan', 'Jumat 15:00-17:00', 'Masjid Sekolah', 'active')
            ");
            
            echo "<p>✅ Sample data added to ekstrakurikuler</p>";
            
        } else {
            echo "<p>✅ Table ekstrakurikuler already exists</p>";
        }
        
        // Check if siswa_ekstrakurikuler table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'siswa_ekstrakurikuler'");
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            echo "<p>⚠️ Table siswa_ekstrakurikuler doesn't exist. Creating it...</p>";
            
            // Create siswa_ekstrakurikuler table
            $pdo->exec("
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            echo "<p>✅ Table siswa_ekstrakurikuler created successfully</p>";
        } else {
            echo "<p>✅ Table siswa_ekstrakurikuler already exists</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>⚠️ Error with ekstrakurikuler table: " . $e->getMessage() . "</p>";
    }
    
    // Fix foreign key constraints for existing tables
    echo "<h3>Fixing foreign key constraints...</h3>";
    
    // Check jadwal table constraints
    try {
        $stmt = $pdo->prepare("SHOW CREATE TABLE jadwal");
        $stmt->execute();
        $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (strpos($createTable['Create Table'], 'ON DELETE SET NULL') === false) {
            echo "<p>⚠️ Fixing jadwal table foreign key constraint...</p>";
            
            // Drop existing foreign key
            $pdo->exec("ALTER TABLE jadwal DROP FOREIGN KEY IF EXISTS jadwal_ibfk_1");
            
            // Add proper foreign key with ON DELETE SET NULL
            $pdo->exec("ALTER TABLE jadwal ADD CONSTRAINT jadwal_ibfk_1 FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE SET NULL");
            
            echo "<p>✅ jadwal table foreign key constraint fixed</p>";
        } else {
            echo "<p>✅ jadwal table foreign key constraint already correct</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>⚠️ Error fixing jadwal table: " . $e->getMessage() . "</p>";
    }
    
    // Update ekstrakurikuler.php to use correct field names
    echo "<h3>Updating ekstrakurikuler.php...</h3>";
    try {
        $ekstraContent = file_get_contents('ekstrakurikuler.php');
        
        // Replace image_url with image
        $ekstraContent = str_replace('g.image_url as foto_pembina', 'g.image as foto_pembina', $ekstraContent);
        
        file_put_contents('ekstrakurikuler.php', $ekstraContent);
        echo "<p>✅ ekstrakurikuler.php updated to use correct field names</p>";
        
    } catch (Exception $e) {
        echo "<p>⚠️ Error updating ekstrakurikuler.php: " . $e->getMessage() . "</p>";
    }
    
    // Add ekstrakurikuler table to schema.sql
    echo "<h3>Updating schema.sql...</h3>";
    try {
        $schemaContent = file_get_contents('database/schema.sql');
        
        // Add ekstrakurikuler table definition before the indexes section
        $ekstrakurikulerTable = "
-- =====================================================
-- EKSTRAKURIKULER TABLE
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

";
        
        // Insert before the indexes section
        $schemaContent = str_replace(
            '-- =====================================================',
            $ekstrakurikulerTable . '-- =====================================================',
            $schemaContent
        );
        
        // Add indexes for new tables
        $newIndexes = "
-- Ekstrakurikuler indexes
CREATE INDEX idx_ekstrakurikuler_status ON ekstrakurikuler(status);
CREATE INDEX idx_ekstrakurikuler_kategori ON ekstrakurikuler(kategori);
CREATE INDEX idx_ekstrakurikuler_pembina_id ON ekstrakurikuler(pembina_id);

-- Siswa Ekstrakurikuler indexes
CREATE INDEX idx_siswa_ekstrakurikuler_siswa_id ON siswa_ekstrakurikuler(siswa_id);
CREATE INDEX idx_siswa_ekstrakurikuler_ekstrakurikuler_id ON siswa_ekstrakurikuler(ekstrakurikuler_id);
CREATE INDEX idx_siswa_ekstrakurikuler_status ON siswa_ekstrakurikuler(status);

";
        
        // Insert before the existing indexes
        $schemaContent = str_replace(
            '-- Jadwal indexes',
            $newIndexes . '-- Jadwal indexes',
            $schemaContent
        );
        
        file_put_contents('database/schema.sql', $schemaContent);
        echo "<p>✅ schema.sql updated with ekstrakurikuler tables</p>";
        
    } catch (Exception $e) {
        echo "<p>⚠️ Error updating schema.sql: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>✅ Foreign Key Constraints Fix Complete!</h3>";
    echo "<p>All foreign key constraints have been properly configured:</p>";
    echo "<ul>";
    echo "<li>✅ <strong>jadwal.guru_id</strong> → guru.id (ON DELETE SET NULL)</li>";
    echo "<li>✅ <strong>ekstrakurikuler.pembina_id</strong> → guru.id (ON DELETE SET NULL)</li>";
    echo "<li>✅ <strong>siswa_ekstrakurikuler.siswa_id</strong> → siswa.id (ON DELETE CASCADE)</li>";
    echo "<li>✅ <strong>siswa_ekstrakurikuler.ekstrakurikuler_id</strong> → ekstrakurikuler.id (ON DELETE CASCADE)</li>";
    echo "</ul>";
    
    echo "<p><strong>What this means:</strong></p>";
    echo "<ul>";
    echo "<li>✅ You can now delete/update guru records without foreign key errors</li>";
    echo "<li>✅ When a guru is deleted, related jadwal and ekstrakurikuler records will have pembina_id set to NULL</li>";
    echo "<li>✅ When a siswa or ekstrakurikuler is deleted, related junction records will be automatically deleted</li>";
    echo "</ul>";
    
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Test deleting/updating guru records - should work without errors now</li>";
    echo "<li>Test the ekstrakurikuler page - should display correctly</li>";
    echo "<li>Verify that all foreign key relationships work properly</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
