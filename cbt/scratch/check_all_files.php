<?php
$dir = 'c:/xampp/htdocs/moodle/cbt';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $path => $item) {
    if ($item->isDir()) continue;
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if (!in_array($ext, ['php', 'js', 'html', 'css'])) continue;
    if (strpos($path, 'scratch') !== false) continue;
    if (strpos($path, '.bak') !== false) continue;

    $content = file_get_contents($path);
    if (preg_match_all('/[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}]/u', $content, $matches)) {
        echo basename($path) . " has emojis: " . implode(' ', array_unique($matches[0])) . "\n";
    }
}
echo "Check completed.\n";

