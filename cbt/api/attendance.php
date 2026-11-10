<?php
// api/attendance.php — E-Learning SMKN 1 CIBINONG (Enhanced Presensi Input & Rekap)
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
    exit;
}

$uid    = (int)$_SESSION['user_id'];
$role   = $_SESSION['role'] ?? 'siswa';
$method = $_SERVER['REQUEST_METHOD'];

// ── GET ────────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $action   = $_GET['action'] ?? 'list';
    $classId  = (int)($_GET['class_id']  ?? 0);
    $courseId = (int)($_GET['course_id'] ?? 0);
    $date     = trim($_GET['date'] ?? date('Y-m-d'));

    if ($action === 'list') {
        if (!$classId) {
            echo json_encode(['success' => false, 'message' => 'ID Kelas wajib dipilih.']);
            exit;
        }

        // List attendance for a class on a specific date (and optional course)
        if ($courseId > 0) {
            $sql = "
                SELECT u.id AS student_id, u.name, u.nip_nis, u.username,
                       a.status, a.is_late, a.notes, a.id AS attendance_id
                FROM users u
                LEFT JOIN attendance a ON a.student_id = u.id AND a.date = ? AND a.course_id = ?
                WHERE u.class_id = ? AND u.role = 'siswa'
                ORDER BY u.name ASC
            ";
            $q = $pdo->prepare($sql);
            $q->execute([$date, $courseId, $classId]);
        } else {
            $sql = "
                SELECT u.id AS student_id, u.name, u.nip_nis, u.username,
                       a.status, a.is_late, a.notes, a.id AS attendance_id
                FROM users u
                LEFT JOIN attendance a ON a.student_id = u.id AND a.date = ? AND (a.course_id IS NULL OR a.course_id = 0)
                WHERE u.class_id = ? AND u.role = 'siswa'
                ORDER BY u.name ASC
            ";
            $q = $pdo->prepare($sql);
            $q->execute([$date, $classId]);
        }

        $rawRows = $q->fetchAll();
        $students = [];
        $seen = [];
        foreach ($rawRows as $r) {
            $sid = (int)$r['student_id'];
            if (isset($seen[$sid])) continue;
            $seen[$sid] = true;
            $students[] = [
                'student_id'    => $sid,
                'name'          => $r['name'],
                'nip_nis'       => $r['nip_nis'] ?: ($r['username'] ?: '—'),
                'status'        => $r['status'] ?: 'hadir',
                'is_late'       => (int)($r['is_late'] ?? 0),
                'notes'         => $r['notes'] ?: '',
                'attendance_id' => $r['attendance_id'] ? (int)$r['attendance_id'] : null
            ];
        }

        echo json_encode([
            'success'  => true,
            'data'     => $students,
            'class_id' => $classId,
            'course_id'=> $courseId,
            'date'     => $date
        ]);
        exit;
    }

    if ($action === 'my_history') {
        // Student: own attendance history (last 60 records)
        $q = $pdo->prepare("
            SELECT a.date, a.status, a.is_late, a.notes, COALESCE(c.course_name, 'Presensi Harian Umum') as course_name
            FROM attendance a 
            LEFT JOIN courses c ON a.course_id = c.id
            WHERE a.student_id = ?
            ORDER BY a.date DESC LIMIT 60
        ");
        $q->execute([$uid]);
        echo json_encode(['success' => true, 'data' => $q->fetchAll()]);
        exit;
    }

    if ($action === 'summary') {
        if (!$classId) {
            echo json_encode(['success' => false, 'message' => 'class_id required']);
            exit;
        }
        $q = $pdo->prepare("
            SELECT u.id, u.name, u.nip_nis,
              COUNT(CASE WHEN a.status='hadir' THEN 1 END) AS hadir,
              COUNT(CASE WHEN a.status='izin'  THEN 1 END) AS izin,
              COUNT(CASE WHEN a.status='sakit' THEN 1 END) AS sakit,
              COUNT(CASE WHEN a.status='alpa'  THEN 1 END) AS alpa,
              COUNT(a.id)                                    AS total,
              COUNT(CASE WHEN a.is_late=1 THEN 1 END)       AS terlambat,
              ROUND(COUNT(CASE WHEN a.status='hadir' THEN 1 END)/NULLIF(COUNT(a.id),0)*100,1) AS pct_hadir
            FROM users u
            LEFT JOIN attendance a ON a.student_id=u.id AND (? = 0 OR a.course_id=?)
            WHERE u.class_id=? AND u.role='siswa'
            GROUP BY u.id ORDER BY u.name
        ");
        $q->execute([$courseId, $courseId, $classId]);
        echo json_encode(['success' => true, 'data' => $q->fetchAll()]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    exit;
}

// ── POST — Bulk Save / Mark All Present ─────────────────────────────────────────
if ($method === 'POST') {
    if (!in_array($role, ['admin', 'guru'])) {
        echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya Admin dan Guru yang dapat mengisi presensi.']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $d   = json_decode($raw, true) ?? $_POST;

    $classId  = (int)($d['class_id']  ?? 0);
    $courseId = (int)($d['course_id'] ?? 0) ?: null;
    $date     = trim($d['date'] ?? date('Y-m-d'));
    $markAll  = !empty($d['mark_all_present']);
    $records  = $d['records'] ?? [];

    if (!$classId) {
        echo json_encode(['success' => false, 'message' => 'Kelas harus dipilih.']);
        exit;
    }

    if ($markAll) {
        // Fetch all students in class
        $st = $pdo->prepare("SELECT id FROM users WHERE class_id = ? AND role = 'siswa'");
        $st->execute([$classId]);
        $studentIds = $st->fetchAll(PDO::FETCH_COLUMN);

        if (empty($studentIds)) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada siswa terdaftar di kelas ini.']);
            exit;
        }

        $marked = 0;
        foreach ($studentIds as $sid) {
            $chk = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ? AND (course_id <=> ?)");
            $chk->execute([$sid, $date, $courseId]);
            $existingId = $chk->fetchColumn();

            if ($existingId) {
                $upd = $pdo->prepare("UPDATE attendance SET class_id = ?, status = 'hadir', is_late = 0, created_by = ? WHERE id = ?");
                $upd->execute([$classId, $uid, $existingId]);
            } else {
                $ins = $pdo->prepare("INSERT INTO attendance (course_id, class_id, student_id, date, status, is_late, created_by) VALUES (?, ?, ?, ?, 'hadir', 0, ?)");
                $ins->execute([$courseId, $classId, $sid, $date, $uid]);
            }
            $marked++;
        }

        echo json_encode([
            'success' => true,
            'marked'  => $marked,
            'message' => "Semua ($marked) siswa berhasil ditandai Hadir!"
        ]);
        exit;
    }

    if (empty($records)) {
        echo json_encode(['success' => false, 'message' => 'Data presensi siswa kosong.']);
        exit;
    }

    $saved = 0;
    foreach ($records as $r) {
        $sid    = (int)($r['student_id'] ?? 0);
        if (!$sid) continue;

        $status = in_array($r['status'] ?? 'hadir', ['hadir', 'izin', 'sakit', 'alpa']) ? $r['status'] : 'hadir';
        $late   = !empty($r['is_late']) ? 1 : 0;
        $notes  = trim($r['notes'] ?? '');

        // Safe upsert check
        $chk = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ? AND (course_id <=> ?)");
        $chk->execute([$sid, $date, $courseId]);
        $existingId = $chk->fetchColumn();

        if ($existingId) {
            $upd = $pdo->prepare("UPDATE attendance SET class_id = ?, status = ?, is_late = ?, notes = ?, created_by = ? WHERE id = ?");
            $upd->execute([$classId, $status, $late, $notes ?: null, $uid, $existingId]);
        } else {
            $ins = $pdo->prepare("INSERT INTO attendance (course_id, class_id, student_id, date, status, is_late, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$courseId, $classId, $sid, $date, $status, $late, $notes ?: null, $uid]);
        }
        $saved++;
    }

    echo json_encode([
        'success' => true,
        'saved'   => $saved,
        'message' => "Presensi $saved siswa berhasil disimpan!"
    ]);
    exit;
}

// ── DELETE single attendance record (admin only) ───────────────────────────────
if ($method === 'DELETE' && $role === 'admin') {
    $d  = json_decode(file_get_contents('php://input'), true) ?? $_GET;
    $id = (int)($d['id'] ?? 0);
    $pdo->prepare("DELETE FROM attendance WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Data presensi berhasil dihapus.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Metode HTTP tidak didukung.']);
