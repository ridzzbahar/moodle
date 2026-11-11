<?php
require_once 'c:/xampp/htdocs/moodle/cbt/db.php';
foreach (['classes', 'courses', 'users', 'quizzes'] as $t) {
    echo "=== $t ===\n";
    $cols = $pdo->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "{$c['Field']} ({$c['Type']})\n";
    }
}
echo "\n--- CLASSES ROWS ---\n";
print_r($pdo->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- COURSES ROWS ---\n";
print_r($pdo->query("SELECT id, course_code, course_name, class_id, major FROM courses")->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- USERS (STUDENTS) ---\n";
print_r($pdo->query("SELECT u.id, u.username, u.name, u.role, u.class_id, cl.class_name, cl.major FROM users u LEFT JOIN classes cl ON u.class_id = cl.id WHERE u.role = 'siswa' LIMIT 10")->fetchAll(PDO::FETCH_ASSOC));
