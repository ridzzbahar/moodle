<?php
// cbt/scratch/test_essay_flow.php
require_once 'c:/xampp/htdocs/moodle/cbt/db.php';

// 1. Get a student and a quiz or create a test quiz
$user = $pdo->query("SELECT id FROM users WHERE role = 'siswa' LIMIT 1")->fetch();
if (!$user) {
    echo "No student found.\n";
    exit;
}
$userId = $user['id'];

// Check or create a test quiz
$stmtQz = $pdo->query("SELECT id FROM quizzes LIMIT 1");
$quiz = $stmtQz->fetch();
if (!$quiz) {
    $pdo->exec("INSERT INTO quizzes (title, description, duration_minutes, kkm, created_by) VALUES ('Test Ujian Campuran', 'Ujian PG & Essay', 30, 75, 1)");
    $quizId = $pdo->lastInsertId();
} else {
    $quizId = $quiz['id'];
}

// Add a test essay question if not exists
$chkEssay = $pdo->prepare("SELECT id FROM questions WHERE quiz_id = ? AND type = 'essay' LIMIT 1");
$chkEssay->execute([$quizId]);
$essayQ = $chkEssay->fetch();

if (!$essayQ) {
    $ins = $pdo->prepare("INSERT INTO questions (quiz_id, type, question_text, essay_answer, points, explanation) VALUES (?, 'essay', ?, ?, ?, ?)");
    $ins->execute([
        $quizId,
        'Jelaskan perbedaan utama antara topologi Star dan topologi Bus dalam jaringan komputer!',
        'Topologi Star menggunakan perangkat konsentrator sentral (switch/hub), bila satu kabel putus jaringan lain tetap berjalan. Topologi Bus menggunakan kabel utama (backbone), bila kabel utama putus seluruh jaringan lumpuh.',
        10,
        'Topologi Star memiliki keandalan lebih tinggi dan mudah dalam troubleshooting, sedangkan topologi Bus lebih hemat kabel namun memiliki kelemahan fatal berupa single point of failure pada kabel backbone.'
    ]);
    $essayQId = $pdo->lastInsertId();
    echo "Created sample essay question ID: $essayQId\n";
} else {
    $essayQId = $essayQ['id'];
    echo "Using existing essay question ID: $essayQId\n";
}

// 2. Start attempt simulation
$pdo->prepare("DELETE FROM exam_attempts WHERE user_id = ? AND quiz_id = ?")->execute([$userId, $quizId]);

$insAtt = $pdo->prepare("INSERT INTO exam_attempts (quiz_id, user_id, total_questions, status, started_at) VALUES (?, ?, 2, 'in_progress', NOW())");
$insAtt->execute([$quizId, $userId]);
$attemptId = $pdo->lastInsertId();
echo "Started attempt ID: $attemptId for user $userId\n";

// 3. Save student answer for essay
$studentEssayAnswer = "Topologi star menghubungkan setiap komputer ke switch atau hub sentral sehingga jika satu komputer bermasalah, komputer lain tidak terganggu. Sedangkan topologi bus menggunakan satu kabel utama panjang, sehingga bila kabel utama putus maka seluruh jaringan akan terputus.";
$insAns = $pdo->prepare("INSERT INTO exam_answers (attempt_id, question_id, essay_response) VALUES (?, ?, ?)");
$insAns->execute([$attemptId, $essayQId, $studentEssayAnswer]);
echo "Saved essay response for question $essayQId\n";

// 4. Test review query
$stmtReview = $pdo->prepare("
    SELECT q.id, q.type, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d,
           UPPER(TRIM(q.correct_option)) as correct_option, q.essay_answer, q.explanation, q.points,
           UPPER(TRIM(COALESCE(ea.selected_option, ''))) as selected_option,
           COALESCE(ea.essay_response, '') as essay_response,
           COALESCE(ea.is_correct, 0) as is_correct
    FROM questions q
    LEFT JOIN exam_answers ea ON ea.question_id = q.id AND ea.attempt_id = ?
    WHERE q.id = ?
");
$stmtReview->execute([$attemptId, $essayQId]);
$reviewItem = $stmtReview->fetch();

echo "\n--- Verification of Review Item ---\n";
echo "Type: " . $reviewItem['type'] . "\n";
echo "Question: " . $reviewItem['question_text'] . "\n";
echo "Student Essay Response: " . $reviewItem['essay_response'] . "\n";
echo "Teacher Rubric (essay_answer): " . $reviewItem['essay_answer'] . "\n";
echo "Explanation: " . $reviewItem['explanation'] . "\n";
echo "------------------------------------\n";

if ($reviewItem['type'] === 'essay' && !empty($reviewItem['essay_response']) && !empty($reviewItem['explanation'])) {
    echo "SUCCESS: Essay question and explanation flow works properly!\n";
} else {
    echo "FAIL: Something is missing in review item.\n";
}

