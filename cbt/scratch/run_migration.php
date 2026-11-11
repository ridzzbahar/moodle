<?php
require_once 'c:/xampp/htdocs/moodle/cbt/db.php';
try { $pdo->exec("ALTER TABLE `course_materials` MODIFY COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'text';"); } catch (Exception $e) {}
try { $pdo->exec("ALTER TABLE `course_materials` ADD COLUMN `file_name` VARCHAR(255) DEFAULT NULL;"); } catch (Exception $e) {}
try { $pdo->exec("ALTER TABLE `course_materials` ADD COLUMN `file_size` INT DEFAULT 0;"); } catch (Exception $e) {}
try { $pdo->exec("ALTER TABLE `classes` ADD COLUMN `grade_level` INT DEFAULT NULL;"); } catch (Exception $e) {}

// Populate grade_level for existing classes
$classes = $pdo->query("SELECT id, class_name FROM classes")->fetchAll(PDO::FETCH_ASSOC);
foreach ($classes as $cl) {
    $lvl = null;
    if (preg_match('/\b(?:XII|12)\b/i', $cl['class_name'])) $lvl = 12;
    elseif (preg_match('/\b(?:XI|11)\b/i', $cl['class_name'])) $lvl = 11;
    elseif (preg_match('/\b(?:X|10)\b/i', $cl['class_name'])) $lvl = 10;
    if ($lvl) {
        $pdo->prepare("UPDATE classes SET grade_level = ? WHERE id = ?")->execute([$lvl, $cl['id']]);
        echo "Class {$cl['id']} ({$cl['class_name']}) set to grade level $lvl\n";
    }
}
echo "Migration completed.\n";

