<?php
// Test file untuk API berita
require_once '../config/database.php';

echo "<h2>Test API Berita</h2>";

try {
    $pdo = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Test get all berita
    echo "<h3>1. Test Get All Berita:</h3>";
    $stmt = $pdo->query("SELECT id, title, excerpt, content, author, status FROM berita ORDER BY created_at DESC LIMIT 3");
    $berita = $stmt->fetchAll();
    
    if (count($berita) > 0) {
        echo "<p>✅ Found " . count($berita) . " berita</p>";
        foreach ($berita as $item) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
            echo "<strong>ID:</strong> " . $item['id'] . "<br>";
            echo "<strong>Title:</strong> " . htmlspecialchars($item['title']) . "<br>";
            echo "<strong>Excerpt:</strong> " . htmlspecialchars(substr($item['excerpt'] ?? '', 0, 100)) . "<br>";
            echo "<strong>Content:</strong> " . htmlspecialchars(substr($item['content'] ?? '', 0, 100)) . "<br>";
            echo "<strong>Author:</strong> " . htmlspecialchars($item['author']) . "<br>";
            echo "<strong>Status:</strong> " . $item['status'];
            echo "</div>";
        }
        
        // Test get single berita
        $firstId = $berita[0]['id'];
        echo "<h3>2. Test Get Single Berita (ID: $firstId):</h3>";
        $stmt = $pdo->prepare("SELECT * FROM berita WHERE id = ?");
        $stmt->execute([$firstId]);
        $singleBerita = $stmt->fetch();
        
        if ($singleBerita) {
            echo "<p>✅ Single berita loaded successfully</p>";
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 5px; background: #f8f9fa;'>";
            echo "<strong>ID:</strong> " . $singleBerita['id'] . "<br>";
            echo "<strong>Title:</strong> " . htmlspecialchars($singleBerita['title']) . "<br>";
            echo "<strong>Excerpt:</strong> " . htmlspecialchars($singleBerita['excerpt'] ?? '') . "<br>";
            echo "<strong>Content:</strong> " . htmlspecialchars($singleBerita['content'] ?? '') . "<br>";
            echo "<strong>Author:</strong> " . htmlspecialchars($singleBerita['author']) . "<br>";
            echo "<strong>Status:</strong> " . $singleBerita['status'];
            echo "</div>";
        } else {
            echo "<p>❌ Failed to load single berita</p>";
        }
        
    } else {
        echo "<p>❌ No berita found in database</p>";
    }
    
    // Test table structure
    echo "<h3>3. Test Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE berita");
    $columns = $stmt->fetchAll();
    
    echo "<p>✅ Table structure:</p>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li><strong>" . $column['Field'] . "</strong> - " . $column['Type'] . " (" . $column['Null'] . ")</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='berita.php'>Back to Berita Admin</a></p>";
?>
