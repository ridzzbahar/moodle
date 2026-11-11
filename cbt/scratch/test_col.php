<?php
require_once 'c:/xampp/htdocs/moodle/cbt/db.php';
try {
    $pdo->exec("ALTER TABLE `questions` ADD COLUMN `explanation` TEXT DEFAULT NULL;");
    echo "Added explanation column successfully.\n";
} catch (Exception $e) {
    echo "Notice: " . $e->getMessage() . "\n";
}
$cols = $pdo->query("DESCRIBE questions")->fetchAll(PDO::FETCH_COLUMN);
echo "Questions columns: " . implode(', ', $cols) . "\n";

