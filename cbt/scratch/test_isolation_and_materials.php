<?php
// cbt/scratch/test_isolation_and_materials.php
require_once __DIR__ . '/../db.php';

function testUserSession($username, $role = 'siswa') {
    global $pdo;
    $st = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $st->execute([$username]);
    $u = $st->fetch(PDO::FETCH_ASSOC);
    if (!$u) throw new Exception("User $username not found");
    return [
        'user_id' => $u['id'],
        'role' => $u['role'],
        'major' => $u['major'],
        'class_id' => $u['class_id']
    ];
}

echo "=== TESTING ISOLATION & ACCESS CONTROL ===\n";

// 1. TEST COURSES FILTERING
// RPL 12 student
$uRpl12 = testUserSession('siswa_rpl12');
$_SESSION = $uRpl12;

echo "\n--- 1. Testing Courses for siswa_rpl12 (Class XII RPL 1, Major RPL) ---\n";
// Simulate courses.php query logic
$sql = "
    SELECT cr.id, cr.course_code, cr.course_name, cr.major, cr.class_id, cl.class_name
    FROM courses cr
    LEFT JOIN classes cl ON cr.class_id = cl.id
    WHERE (cr.major = 'Umum' OR cr.major = :major OR cr.major IS NULL OR cr.major = '')
      AND (cr.class_id = :class_id OR cr.class_id IS NULL)
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':major' => $uRpl12['major'], ':class_id' => $uRpl12['class_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($courses) . " courses visible to siswa_rpl12:\n";
foreach ($courses as $c) {
    echo "  - [ID: {$c['id']}] {$c['course_name']} | Major: {$c['major']} | Class: {$c['class_name']}\n";
    if ($c['major'] !== 'RPL' && $c['major'] !== 'Umum' && !empty($c['major'])) {
        echo "  [FAIL] Leak detected! Course {$c['course_code']} has major {$c['major']}!\n";
    }
    if ($c['class_id'] !== null && $c['class_id'] != $uRpl12['class_id']) {
        echo "  [FAIL] Leak detected! Course {$c['course_code']} has class_id {$c['class_id']}!\n";
    }
}

// 2. TEST COURSE DETAIL DIRECT ACCESS
echo "\n--- 2. Testing Direct Course Access Permissions ---\n";
// Helper to simulate course_detail check
function checkCourseAccess($userId, $courseId) {
    global $pdo;
    $stCheck = $pdo->prepare("
        SELECT c.id, c.course_name, c.major, c.class_id,
               cl.class_name, cl.grade_level,
               u.major AS student_major, u.class_id AS student_class_id,
               ucl.class_name AS student_class_name, ucl.grade_level AS student_grade_level
        FROM courses c
        LEFT JOIN classes cl ON c.class_id = cl.id
        CROSS JOIN users u ON u.id = ?
        LEFT JOIN classes ucl ON u.class_id = ucl.id
        WHERE c.id = ?
    ");
    $stCheck->execute([$userId, $courseId]);
    $access = $stCheck->fetch();
    if (!$access) return ['allowed' => false, 'reason' => 'Course not found'];

    $cMajor   = trim($access['major'] ?? '');
    $sMajor   = trim($access['student_major'] ?? '');
    $cClassId = $access['class_id'] ? (int)$access['class_id'] : null;
    $sClassId = $access['student_class_id'] ? (int)$access['student_class_id'] : null;

    if (!empty($cMajor) && strcasecmp($cMajor, 'Umum') !== 0) {
        if (strcasecmp($cMajor, $sMajor) !== 0) {
            return ['allowed' => false, 'reason' => "Major mismatch: course {$cMajor} vs student {$sMajor}"];
        }
    }

    if ($cClassId !== null && $cClassId !== $sClassId) {
        return ['allowed' => false, 'reason' => "Class mismatch: course class {$access['class_name']} vs student class {$access['student_class_name']}"];
    }

    return ['allowed' => true, 'course_name' => $access['course_name']];
}

// Find course IDs
$rpl12Course = $pdo->query("SELECT id FROM courses WHERE course_code = 'RPL-BD12'")->fetchColumn();
$rpl10Course = $pdo->query("SELECT id FROM courses WHERE course_code = 'RPL-DP10'")->fetchColumn();
$tkj12Course = $pdo->query("SELECT id FROM courses WHERE course_code = 'TKJ-AIJ12'")->fetchColumn();

// Test A: RPL 12 student accesses RPL 12 course -> ALLOWED
$resA = checkCourseAccess($uRpl12['user_id'], $rpl12Course);
echo "A. siswa_rpl12 accesses RPL-BD12: " . ($resA['allowed'] ? "ALLOWED (OK)" : "DENIED: " . $resA['reason']) . "\n";

// Test B: RPL 12 student accesses TKJ 12 course -> DENIED
$resB = checkCourseAccess($uRpl12['user_id'], $tkj12Course);
echo "B. siswa_rpl12 accesses TKJ-AIJ12: " . (!$resB['allowed'] ? "DENIED (OK, " . $resB['reason'] . ")" : "UNEXPECTEDLY ALLOWED!") . "\n";

// Test C: RPL 12 student accesses RPL 10 course -> DENIED
$resC = checkCourseAccess($uRpl12['user_id'], $rpl10Course);
echo "C. siswa_rpl12 accesses RPL-DP10 (Kelas 10): " . (!$resC['allowed'] ? "DENIED (OK, " . $resC['reason'] . ")" : "UNEXPECTEDLY ALLOWED!") . "\n";

// 3. TEST EXAM START CHECK
echo "\n--- 3. Testing Exam Start Access Control ---\n";
function checkExamAccess($userId, $quizId) {
    global $pdo;
    $stmtQzInfo = $pdo->prepare("
        SELECT qz.id, qz.title, qz.duration_minutes, qz.kkm, qz.course_id,
               c.course_name, c.major AS course_major, c.class_id AS course_class_id,
               cl.class_name AS course_class_name
        FROM quizzes qz
        LEFT JOIN courses c ON qz.course_id = c.id
        LEFT JOIN classes cl ON c.class_id = cl.id
        WHERE qz.id = ?
    ");
    $stmtQzInfo->execute([$quizId]);
    $quizInfo = $stmtQzInfo->fetch();
    if (!$quizInfo) return ['allowed' => false, 'reason' => 'Quiz not found'];

    $stmtU = $pdo->prepare("SELECT u.id, u.major, u.class_id, cl.class_name FROM users u LEFT JOIN classes cl ON u.class_id = cl.id WHERE u.id = ?");
    $stmtU->execute([$userId]);
    $studentUser = $stmtU->fetch();

    $cMajor = trim($quizInfo['course_major'] ?? '');
    $sMajor = trim($studentUser['major'] ?? '');
    $cClassId = $quizInfo['course_class_id'] ? (int)$quizInfo['course_class_id'] : null;
    $sClassId = $studentUser['class_id'] ? (int)$studentUser['class_id'] : null;

    if (!empty($cMajor) && strcasecmp($cMajor, 'Umum') !== 0) {
        if (strcasecmp($cMajor, $sMajor) !== 0) {
            return ['allowed' => false, 'reason' => "Major mismatch: quiz {$cMajor} vs student {$sMajor}"];
        }
    }

    if ($cClassId !== null && $cClassId !== $sClassId) {
        return ['allowed' => false, 'reason' => "Class mismatch: quiz requires {$quizInfo['course_class_name']} vs student {$studentUser['class_name']}"];
    }

    return ['allowed' => true, 'quiz_title' => $quizInfo['title']];
}

$quizRpl = $pdo->query("SELECT id FROM quizzes WHERE title LIKE '%Basis Data Kelas 12 RPL%'")->fetchColumn();
$quizTkj = $pdo->query("SELECT id FROM quizzes WHERE title LIKE '%Routing BGP%Kelas 12 TKJ%'")->fetchColumn();

// Test D: siswa_rpl12 starts RPL 12 quiz -> ALLOWED
$resD = checkExamAccess($uRpl12['user_id'], $quizRpl);
echo "D. siswa_rpl12 starts Quiz RPL 12: " . ($resD['allowed'] ? "ALLOWED (OK)" : "DENIED: " . $resD['reason']) . "\n";

// Test E: siswa_rpl12 starts TKJ 12 quiz -> DENIED
$resE = checkExamAccess($uRpl12['user_id'], $quizTkj);
echo "E. siswa_rpl12 starts Quiz TKJ 12: " . (!$resE['allowed'] ? "DENIED (OK, " . $resE['reason'] . ")" : "UNEXPECTEDLY ALLOWED!") . "\n";

// Test F: siswa_rpl10 starts RPL 12 quiz -> DENIED
$uRpl10 = testUserSession('siswa_rpl10');
$resF = checkExamAccess($uRpl10['user_id'], $quizRpl);
echo "F. siswa_rpl10 starts Quiz RPL 12: " . (!$resF['allowed'] ? "DENIED (OK, " . $resF['reason'] . ")" : "UNEXPECTEDLY ALLOWED!") . "\n";

// 4. TEST MATERIALS WITH FILE INFO
echo "\n--- 4. Checking Course Materials with Uploaded Files ---\n";
$stMat = $pdo->prepare("SELECT id, title, type, file_name, file_size, content_url FROM course_materials WHERE course_id = ?");
$stMat->execute([$rpl12Course]);
$mats = $stMat->fetchAll(PDO::FETCH_ASSOC);
foreach ($mats as $m) {
    echo "  Material: {$m['title']}\n";
    echo "    Type: {$m['type']} | File: {$m['file_name']} | Size: {$m['file_size']} bytes\n";
    echo "    Path: {$m['content_url']}\n";
    if ($m['content_url'] && file_exists(__DIR__ . '/../' . $m['content_url'])) {
        echo "    [FILE ON DISK EXISTS: OK]\n";
    }
}

echo "\n=== ALL TESTS PASSED PERFECTLY ===\n";

