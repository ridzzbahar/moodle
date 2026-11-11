<?php
require_once 'c:/xampp/htdocs/moodle/cbt/db.php';
$cols = $pdo->query("DESCRIBE course_materials")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

