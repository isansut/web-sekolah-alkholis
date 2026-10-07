<?php
/**
 * Fix All Image Field References
 * Updates all remaining image_url and photo_url references to use consistent field names
 */

require_once 'config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    echo "<h2>Fixing All Image Field References</h2>";
    echo "<p>Updating all remaining image_url and photo_url references to use consistent field names...</p>";
    
    // Files to update with their replacements
    $filesToUpdate = [
        'admin/api/berita_upload.php' => [
            "'image_url' => \$publicBase . \$filename" => "'image' => \$publicBase . \$filename"
        ],
        'admin/api/galeri.php' => [
            "\$image_url = \$_POST['image_url'] ?? ''" => "\$image = \$_POST['image'] ?? ''",
            "empty(\$title) || empty(\$image_url)" => "empty(\$title) || empty(\$image)",
            "\$image_url," => "\$image,",
            "\$image_url = \$putData['image_url'] ?? ''" => "\$image = \$putData['image'] ?? ''",
            "empty(\$id) || empty(\$title) || empty(\$image_url)" => "empty(\$id) || empty(\$title) || empty(\$image)"
        ],
        'admin/karya_siswa.php' => [
            "karya.image_url" => "karya.image",
            "json.image_url" => "json.image",
            "'image_url'" => "'image'"
        ],
        'admin/guru.php' => [
            "database field image_url terupdate" => "database field image terupdate"
        ],
        'admin/siswa.php' => [
            "name=\"photo_url\"" => "name=\"photo\"",
            "json.image_url || json.photo_url" => "json.image || json.photo",
            "database field photo_url terupdate" => "database field photo terupdate"
        ],
        'admin/api/siswa.php' => [
            "photo_url VARCHAR(500)" => "photo VARCHAR(500)",
            "\$data['photo_url']" => "\$data['photo']",
            "photo_url" => "photo"
        ],
        'admin/api/siswa_upload.php' => [
            "'image_url' => \$url, 'photo_url' => \$url" => "'image' => \$url, 'photo' => \$url"
        ]
    ];
    
    $totalFiles = 0;
    $updatedFiles = 0;
    
    foreach ($filesToUpdate as $filePath => $replacements) {
        $totalFiles++;
        
        if (!file_exists($filePath)) {
            echo "<p>⚠️ File not found: $filePath</p>";
            continue;
        }
        
        echo "<h3>Updating: $filePath</h3>";
        
        try {
            $content = file_get_contents($filePath);
            $originalContent = $content;
            $changesMade = 0;
            
            foreach ($replacements as $search => $replace) {
                if (strpos($content, $search) !== false) {
                    $content = str_replace($search, $replace, $content);
                    $changesMade++;
                    echo "<p>✅ Replaced: $search → $replace</p>";
                }
            }
            
            if ($changesMade > 0) {
                if (file_put_contents($filePath, $content)) {
                    echo "<p>✅ File updated successfully with $changesMade changes</p>";
                    $updatedFiles++;
                } else {
                    echo "<p>⚠️ Failed to write file: $filePath</p>";
                }
            } else {
                echo "<p>ℹ️ No changes needed for: $filePath</p>";
            }
            
        } catch (Exception $e) {
            echo "<p>⚠️ Error processing file $filePath: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>Summary</h3>";
    echo "<p>Total files processed: $totalFiles</p>";
    echo "<p>Files updated: $updatedFiles</p>";
    
    // Also check for any remaining references
    echo "<h3>Checking for Remaining References</h3>";
    
    $remainingFiles = [];
    $searchPatterns = ['image_url', 'photo_url'];
    
    foreach ($searchPatterns as $pattern) {
        $grepResults = shell_exec("grep -r \"$pattern\" --include=\"*.php\" . 2>/dev/null | head -20");
        if ($grepResults) {
            $remainingFiles[] = $pattern;
            echo "<p>⚠️ Found remaining references to: $pattern</p>";
            echo "<pre>" . htmlspecialchars($grepResults) . "</pre>";
        }
    }
    
    if (empty($remainingFiles)) {
        echo "<p>✅ All image field references have been updated!</p>";
    } else {
        echo "<p>⚠️ Some references may still exist. Please review manually.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Image Field References</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2, h3 { color: #333; }
        .success { color: #28a745; }
        .warning { color: #ffc107; }
        .error { color: #dc3545; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Image Field References Fix Complete</h1>
    <p>This script has updated all PHP files to use consistent image field names:</p>
    <ul>
        <li><strong>image_url</strong> → <strong>image</strong> (for guru, berita, galeri, karya_siswa)</li>
        <li><strong>photo_url</strong> → <strong>photo</strong> (for siswa)</li>
    </ul>
    
    <h2>Next Steps</h2>
    <ol>
        <li>Test the website to ensure all pages load correctly</li>
        <li>Test the admin panel to ensure all CRUD operations work</li>
        <li>Verify that image uploads and displays work properly</li>
        <li>Check that the database schema is consistent</li>
    </ol>
    
    <p><a href="index.php">← Back to Homepage</a></p>
</body>
</html>
