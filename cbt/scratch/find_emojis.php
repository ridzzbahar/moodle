<?php
$files = [
    'c:/xampp/htdocs/moodle/cbt/index.php',
    'c:/xampp/htdocs/moodle/cbt/js/exam_guard.js',
    'c:/xampp/htdocs/moodle/cbt/api/exam.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $lines = file($file);
    foreach ($lines as $i => $line) {
        if (preg_match_all('/[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}]/u', $line, $matches)) {
            echo basename($file) . ':' . ($i+1) . ' ' . implode(' ', $matches[0]) . ' -> ' . substr(trim($line), 0, 90) . "\n";
        }
    }
}
