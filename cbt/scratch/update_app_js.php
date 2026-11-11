<?php
// cbt/scratch/update_app_js.php
$filePath = 'c:/xampp/htdocs/moodle/cbt/js/app.js';
$content = file_get_contents($filePath);

// Backup original file
file_put_contents($filePath . '.bak', $content);

echo "Backup created at app.js.bak. Original length: " . strlen($content) . "\n";

