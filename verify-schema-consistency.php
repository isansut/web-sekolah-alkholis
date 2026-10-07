<?php
/**
 * Verify Database Schema Consistency
 * Checks that all image fields are properly standardized
 */

require_once 'config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    echo "<h2>Database Schema Consistency Verification</h2>";
    echo "<p>Verifying that all image fields are properly standardized...</p>";
    
    $tables = [
        'guru' => 'image',
        'karya_siswa' => 'image', 
        'berita' => 'image',
        'galeri' => 'image',
        'siswa' => 'photo'
    ];
    
    $allCorrect = true;
    
    foreach ($tables as $table => $expectedField) {
        echo "<h3>Table: $table</h3>";
        
        try {
            // Check if the expected field exists
            $stmt = $pdo->prepare("SHOW COLUMNS FROM $table LIKE '$expectedField'");
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $column = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p>✅ <strong>$expectedField</strong> field exists: <code>$column[Type]</code></p>";
                
                // Check if there are any old field names
                $oldFields = [];
                if ($expectedField === 'image') {
                    $stmt = $pdo->prepare("SHOW COLUMNS FROM $table LIKE 'image_url'");
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $oldFields[] = 'image_url';
                    }
                } elseif ($expectedField === 'photo') {
                    $stmt = $pdo->prepare("SHOW COLUMNS FROM $table LIKE 'photo_url'");
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $oldFields[] = 'photo_url';
                    }
                }
                
                if (!empty($oldFields)) {
                    echo "<p>⚠️ Old field(s) still exist: " . implode(', ', $oldFields) . "</p>";
                    $allCorrect = false;
                } else {
                    echo "<p>✅ No old field names found</p>";
                }
                
            } else {
                echo "<p>❌ <strong>$expectedField</strong> field is missing!</p>";
                $allCorrect = false;
            }
            
            // Show all columns for reference
            $stmt = $pdo->prepare("DESCRIBE $table");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<details><summary>All columns in $table</summary><ul>";
            foreach ($columns as $column) {
                $field = $column['Field'];
                $type = $column['Type'];
                $null = $column['Null'];
                $key = $column['Key'];
                $default = $column['Default'];
                
                $style = '';
                if ($field === $expectedField) {
                    $style = 'style="color: green; font-weight: bold;"';
                } elseif (in_array($field, ['image_url', 'photo_url'])) {
                    $style = 'style="color: red; font-weight: bold;"';
                }
                
                echo "<li $style><strong>$field</strong> ($type)";
                if ($null === 'NO') echo " NOT NULL";
                if ($key === 'PRI') echo " PRIMARY KEY";
                if ($default !== null) echo " DEFAULT '$default'";
                echo "</li>";
            }
            echo "</ul></details>";
            
        } catch (Exception $e) {
            echo "<p>❌ Error checking table $table: " . $e->getMessage() . "</p>";
            $allCorrect = false;
        }
        
        echo "<hr>";
    }
    
    // Check if any data exists in the tables
    echo "<h3>Data Verification</h3>";
    
    foreach ($tables as $table => $expectedField) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM $table");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = $result['total'];
            
            if ($total > 0) {
                // Check if any records have data in the image/photo field
                $stmt = $pdo->prepare("SELECT COUNT(*) as with_image FROM $table WHERE $expectedField IS NOT NULL AND $expectedField != ''");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $withImage = $result['with_image'];
                
                echo "<p><strong>$table:</strong> $total total records, $withImage with $expectedField</p>";
                
                if ($withImage > 0) {
                    // Show sample data
                    $stmt = $pdo->prepare("SELECT id, $expectedField FROM $table WHERE $expectedField IS NOT NULL AND $expectedField != '' LIMIT 3");
                    $stmt->execute();
                    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo "<details><summary>Sample $expectedField data</summary><ul>";
                    foreach ($samples as $sample) {
                        echo "<li>ID {$sample['id']}: <code>{$sample[$expectedField]}</code></li>";
                    }
                    echo "</ul></details>";
                }
            } else {
                echo "<p><strong>$table:</strong> No records found</p>";
            }
            
        } catch (Exception $e) {
            echo "<p>⚠️ Error checking data in $table: " . $e->getMessage() . "</p>";
        }
    }
    
    // Final summary
    echo "<h3>Verification Summary</h3>";
    
    if ($allCorrect) {
        echo "<p style='color: green; font-size: 18px;'>✅ <strong>All database schema changes have been applied correctly!</strong></p>";
        echo "<p>Your database now uses consistent field names:</p>";
        echo "<ul>";
        echo "<li><strong>guru.image</strong> - for teacher photos</li>";
        echo "<li><strong>karya_siswa.image</strong> - for student work images</li>";
        echo "<li><strong>berita.image</strong> - for news images</li>";
        echo "<li><strong>galeri.image</strong> - for gallery images</li>";
        echo "<li><strong>siswa.photo</strong> - for student photos</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red; font-size: 18px;'>❌ <strong>Some issues were found!</strong></p>";
        echo "<p>Please run the fix script again or manually fix the issues.</p>";
    }
    
    echo "<h3>Next Steps</h3>";
    echo "<ol>";
    echo "<li>Test your admin panels to ensure they work with the new field names</li>";
    echo "<li>Verify that existing images are still displayed correctly</li>";
    echo "<li>Check that new uploads work properly</li>";
    echo "<li>Delete this verification script after confirming everything works</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
