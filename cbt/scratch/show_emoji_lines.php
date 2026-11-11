<?php
$lines = file('c:/xampp/htdocs/moodle/cbt/js/app.js');
foreach ($lines as $i => $line) {
    if (preg_match('/[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}]/u', $line)) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}

