<?php
require_once 'c:/xampp/htdocs/moodle/cbt/db.php';

$iconMap = [
    '📚' => 'fa-book',
    '💻' => 'fa-laptop-code',
    '🌐' => 'fa-network-wired',
    '🔌' => 'fa-plug',
    '📐' => 'fa-ruler-combined',
    '📖' => 'fa-book-open',
    '💼' => 'fa-briefcase'
];

$courses = $pdo->query("SELECT id, course_name, icon FROM courses")->fetchAll();
foreach ($courses as $c) {
    $cur = $c['icon'];
    $new = $iconMap[$cur] ?? (empty($cur) || preg_match('/[\x{1F000}-\x{1FFFF}]/u', $cur) ? 'fa-book' : $cur);
    if ($new !== $cur) {
        $stmt = $pdo->prepare("UPDATE courses SET icon = ? WHERE id = ?");
        $stmt->execute([$new, $c['id']]);
        echo "Updated course {$c['id']} ({$c['course_name']}): $cur -> $new\n";
    }
}
echo "Courses updated.\n";

