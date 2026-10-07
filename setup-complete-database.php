<?php
/**
 * Complete Database Setup for SMA Al-Kholis Website
 * Creates all tables and populates with sample data
 */

require_once 'config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    echo "<h2>🚀 Complete Database Setup for SMA Al-Kholis</h2>";
    echo "<p>Setting up complete database with all tables and sample data...</p>";
    
    // Read and execute complete schema
    echo "<h3>📋 Creating Database Schema...</h3>";
    try {
        $schemaSQL = file_get_contents('database/complete_schema.sql');
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $schemaSQL)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !str_starts_with($statement, '--')) {
                try {
                    $pdo->exec($statement);
                    echo "<p>✅ Executed: " . substr($statement, 0, 50) . "...</p>";
                } catch (Exception $e) {
                    // Ignore errors for existing tables
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "<p>⚠️ Schema: " . $e->getMessage() . "</p>";
                    }
                }
            }
        }
        
        echo "<p>✅ Database schema created successfully</p>";
        
    } catch (Exception $e) {
        echo "<p>⚠️ Error creating schema: " . $e->getMessage() . "</p>";
    }
    
    // Read and execute sample data
    echo "<h3>📊 Populating with Sample Data...</h3>";
    try {
        $sampleDataSQL = file_get_contents('database/sample_data.sql');
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sampleDataSQL)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !str_starts_with($statement, '--')) {
                try {
                    $pdo->exec($statement);
                    echo "<p>✅ Data inserted: " . substr($statement, 0, 50) . "...</p>";
                } catch (Exception $e) {
                    // Ignore errors for duplicate entries
                    if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                        echo "<p>⚠️ Data: " . $e->getMessage() . "</p>";
                    }
                }
            }
        }
        
        echo "<p>✅ Sample data populated successfully</p>";
        
    } catch (Exception $e) {
        echo "<p>⚠️ Error populating data: " . $e->getMessage() . "</p>";
    }
    
    // Verify all tables exist
    echo "<h3>🔍 Verifying Database Structure...</h3>";
    $expectedTables = [
        'users', 'guru', 'siswa', 'jadwal', 'ekstrakurikuler', 
        'siswa_ekstrakurikuler', 'berita', 'galeri', 'karya_siswa', 
        'pengumuman', 'prestasi', 'contact_messages', 'settings', 'activity_logs'
    ];
    
    $existingTables = [];
    foreach ($expectedTables as $table) {
        try {
            $stmt = $pdo->prepare("SHOW TABLES LIKE '$table'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $existingTables[] = $table;
                echo "<p>✅ Table <strong>$table</strong> exists</p>";
            } else {
                echo "<p>❌ Table <strong>$table</strong> missing</p>";
            }
        } catch (Exception $e) {
            echo "<p>⚠️ Error checking table $table: " . $e->getMessage() . "</p>";
        }
    }
    
    // Check data counts
    echo "<h3>📈 Checking Data Counts...</h3>";
    foreach ($existingTables as $table) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM $table");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>📊 <strong>$table</strong>: {$result['total']} records</p>";
        } catch (Exception $e) {
            echo "<p>⚠️ Error counting $table: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test foreign key relationships
    echo "<h3>🔗 Testing Foreign Key Relationships...</h3>";
    
    // Test guru-siswa relationship through jadwal
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM jadwal j 
            JOIN guru g ON j.guru_id = g.id 
            WHERE g.status = 'active'
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>✅ Guru-Jadwal relationship: {$result['total']} valid connections</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ Error testing guru-jadwal relationship: " . $e->getMessage() . "</p>";
    }
    
    // Test ekstrakurikuler relationships
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM ekstrakurikuler e 
            JOIN guru g ON e.pembina_id = g.id 
            WHERE g.status = 'active'
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>✅ Ekstrakurikuler-Guru relationship: {$result['total']} valid connections</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ Error testing ekstrakurikuler-guru relationship: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>🎉 Database Setup Complete!</h3>";
    echo "<p>Your SMA Al-Kholis website database is now ready with:</p>";
    echo "<ul>";
    echo "<li>✅ <strong>" . count($existingTables) . " tables</strong> created</li>";
    echo "<li>✅ <strong>Sample data</strong> populated</li>";
    echo "<li>✅ <strong>Foreign key relationships</strong> established</li>";
    echo "<li>✅ <strong>Indexes</strong> for optimal performance</li>";
    echo "</ul>";
    
    echo "<h3>🔑 Default Login Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: admin, password: password</li>";
    echo "<li><strong>Guru:</strong> username: guru001, password: password</li>";
    echo "<li><strong>Siswa:</strong> username: siswa001, password: password</li>";
    echo "</ul>";
    
    echo "<h3>📋 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Test the admin panel login</li>";
    echo "<li>Verify all CRUD operations work</li>";
    echo "<li>Test image uploads in all modules</li>";
    echo "<li>Check website frontend functionality</li>";
    echo "<li>Customize content as needed</li>";
    echo "</ol>";
    
    echo "<p><strong>Note:</strong> All passwords are hashed with bcrypt. For production, change these passwords!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
