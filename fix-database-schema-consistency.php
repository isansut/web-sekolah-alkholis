<?php
/**
 * Database Schema Consistency Fix
 * Standardizes image field names across all tables
 * 
 * Changes:
 * - guru.image_url -> guru.image
 * - karya_siswa.image_url -> karya_siswa.image
 * - berita.image_url -> berita.image
 * - galeri.image_url -> galeri.image
 * - siswa.photo_url -> siswa.photo
 */

require_once 'config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    echo "<h2>Database Schema Consistency Fix</h2>";
    echo "<p>Standardizing image field names across all tables...</p>";
    
    // Check current schema
    echo "<h3>Current Schema Analysis:</h3>";
    
    $tables = ['guru', 'karya_siswa', 'berita', 'galeri', 'siswa'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("DESCRIBE $table");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Table: $table</h4>";
        echo "<ul>";
        foreach ($columns as $column) {
            $field = $column['Field'];
            $type = $column['Type'];
            $null = $column['Null'];
            $key = $column['Key'];
            $default = $column['Default'];
            
            echo "<li><strong>$field</strong> ($type)";
            if ($null === 'NO') echo " NOT NULL";
            if ($key === 'PRI') echo " PRIMARY KEY";
            if ($default !== null) echo " DEFAULT '$default'";
            echo "</li>";
        }
        echo "</ul>";
    }
    
    // Fix guru table
    echo "<h3>Fixing guru table...</h3>";
    try {
        // Check if image_url column exists
        $stmt = $pdo->prepare("SHOW COLUMNS FROM guru LIKE 'image_url'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            // Rename image_url to image
            $pdo->exec("ALTER TABLE guru CHANGE COLUMN image_url image VARCHAR(500)");
            echo "<p>✅ guru.image_url renamed to guru.image</p>";
        } else {
            echo "<p>ℹ️ guru.image column already exists</p>";
        }
    } catch (Exception $e) {
        echo "<p>⚠️ Error fixing guru table: " . $e->getMessage() . "</p>";
    }
    
    // Fix karya_siswa table
    echo "<h3>Fixing karya_siswa table...</h3>";
    try {
        // Check if image_url column exists
        $stmt = $pdo->prepare("SHOW COLUMNS FROM karya_siswa LIKE 'image_url'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            // Rename image_url to image
            $pdo->exec("ALTER TABLE karya_siswa CHANGE COLUMN image_url image VARCHAR(500)");
            echo "<p>✅ karya_siswa.image_url renamed to karya_siswa.image</p>";
        } else {
            echo "<p>ℹ️ karya_siswa.image column already exists</p>";
        }
    } catch (Exception $e) {
        echo "<p>⚠️ Error fixing karya_siswa table: " . $e->getMessage() . "</p>";
    }
    
    // Fix berita table
    echo "<h3>Fixing berita table...</h3>";
    try {
        // Check if image_url column exists
        $stmt = $pdo->prepare("SHOW COLUMNS FROM berita LIKE 'image_url'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            // Rename image_url to image
            $pdo->exec("ALTER TABLE berita CHANGE COLUMN image_url image VARCHAR(500)");
            echo "<p>✅ berita.image_url renamed to berita.image</p>";
        } else {
            echo "<p>ℹ️ berita.image column already exists</p>";
        }
    } catch (Exception $e) {
        echo "<p>⚠️ Error fixing berita table: " . $e->getMessage() . "</p>";
    }
    
    // Fix galeri table
    echo "<h3>Fixing galeri table...</h3>";
    try {
        // Check if image_url column exists
        $stmt = $pdo->prepare("SHOW COLUMNS FROM galeri LIKE 'image_url'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            // Rename image_url to image
            $pdo->exec("ALTER TABLE galeri CHANGE COLUMN image_url image VARCHAR(500)");
            echo "<p>✅ galeri.image_url renamed to galeri.image</p>";
        } else {
            echo "<p>ℹ️ galeri.image column already exists</p>";
        }
    } catch (Exception $e) {
        echo "<p>⚠️ Error fixing galeri table: " . $e->getMessage() . "</p>";
    }
    
    // Fix siswa table
    echo "<h3>Fixing siswa table...</h3>";
    try {
        // Check if photo_url column exists
        $stmt = $pdo->prepare("SHOW COLUMNS FROM siswa LIKE 'photo_url'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            // Rename photo_url to photo
            $pdo->exec("ALTER TABLE siswa CHANGE COLUMN photo_url photo VARCHAR(500)");
            echo "<p>✅ siswa.photo_url renamed to siswa.photo</p>";
        } else {
            echo "<p>ℹ️ siswa.photo column already exists</p>";
        }
    } catch (Exception $e) {
        echo "<p>⚠️ Error fixing siswa table: " . $e->getMessage() . "</p>";
        // Try to add photo column if it doesn't exist
        try {
            $stmt = $pdo->prepare("SHOW COLUMNS FROM siswa LIKE 'photo'");
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $pdo->exec("ALTER TABLE siswa ADD COLUMN photo VARCHAR(500) AFTER alamat");
                echo "<p>✅ siswa.photo column added</p>";
            }
        } catch (Exception $e2) {
            echo "<p>⚠️ Error adding siswa.photo column: " . $e2->getMessage() . "</p>";
        }
    }
    
    // Verify final schema
    echo "<h3>Final Schema Verification:</h3>";
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("DESCRIBE $table");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Table: $table</h4>";
        echo "<ul>";
        foreach ($columns as $column) {
            $field = $column['Field'];
            $type = $column['Type'];
            
            // Highlight image/photo fields
            if (in_array($field, ['image', 'photo'])) {
                echo "<li><strong style='color: green;'>$field</strong> ($type) ✅</li>";
            } else {
                echo "<li><strong>$field</strong> ($type)</li>";
            }
        }
        echo "</ul>";
    }
    
    // Update schema.sql file
    echo "<h3>Updating schema.sql file...</h3>";
    try {
        $schemaContent = file_get_contents('database/schema.sql');
        
        // Replace field names
        $schemaContent = str_replace('image_url VARCHAR(500)', 'image VARCHAR(500)', $schemaContent);
        $schemaContent = str_replace('photo_url VARCHAR(500)', 'photo VARCHAR(500)', $schemaContent);
        
        file_put_contents('database/schema.sql', $schemaContent);
        echo "<p>✅ schema.sql file updated</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ Error updating schema.sql: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>✅ Database Schema Consistency Fix Complete!</h3>";
    echo "<p>All image fields are now standardized:</p>";
    echo "<ul>";
    echo "<li><strong>guru.image</strong> - for teacher photos</li>";
    echo "<li><strong>karya_siswa.image</strong> - for student work images</li>";
    echo "<li><strong>berita.image</strong> - for news images</li>";
    echo "<li><strong>galeri.image</strong> - for gallery images</li>";
    echo "<li><strong>siswa.photo</strong> - for student photos</li>";
    echo "</ul>";
    
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Test your admin panels to ensure they work with the new field names</li>";
    echo "<li>Verify that existing images are still displayed correctly</li>";
    echo "<li>Check that new uploads work properly</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
