<?php
// cbt/scratch/seed_test_data.php
require_once __DIR__ . '/../db.php';

echo "=== SEEDING TEST DATA FOR RPL, TKJ, DKV (GRADES 10, 11, 12) ===\n";

$passHash = password_hash('password123', PASSWORD_BCRYPT);

// 1. CLASSES
$classes = [
    // RPL
    ['class_name' => 'X RPL 1', 'academic_year' => '2026/2027', 'major' => 'RPL', 'grade_level' => 10],
    ['class_name' => 'XI RPL 1', 'academic_year' => '2026/2027', 'major' => 'RPL', 'grade_level' => 11],
    ['class_name' => 'XII RPL 1', 'academic_year' => '2026/2027', 'major' => 'RPL', 'grade_level' => 12],
    // TKJ
    ['class_name' => 'X TKJ 1', 'academic_year' => '2026/2027', 'major' => 'TKJ', 'grade_level' => 10],
    ['class_name' => 'XI TKJ 1', 'academic_year' => '2026/2027', 'major' => 'TKJ', 'grade_level' => 11],
    ['class_name' => 'XII TKJ 1', 'academic_year' => '2026/2027', 'major' => 'TKJ', 'grade_level' => 12],
    // DKV
    ['class_name' => 'X DKV 1', 'academic_year' => '2026/2027', 'major' => 'DKV', 'grade_level' => 10],
    ['class_name' => 'XI DKV 1', 'academic_year' => '2026/2027', 'major' => 'DKV', 'grade_level' => 11],
    ['class_name' => 'XII DKV 1', 'academic_year' => '2026/2027', 'major' => 'DKV', 'grade_level' => 12],
];

$classMap = [];
foreach ($classes as $cl) {
    $st = $pdo->prepare("SELECT id FROM classes WHERE class_name = ?");
    $st->execute([$cl['class_name']]);
    $id = $st->fetchColumn();
    if (!$id) {
        $ins = $pdo->prepare("INSERT INTO classes (class_name, academic_year, major, grade_level) VALUES (?, ?, ?, ?)");
        $ins->execute([$cl['class_name'], $cl['academic_year'], $cl['major'], $cl['grade_level']]);
        $id = $pdo->lastInsertId();
        echo "Created class: {$cl['class_name']} (ID: $id)\n";
    } else {
        $pdo->prepare("UPDATE classes SET major = ?, grade_level = ? WHERE id = ?")->execute([$cl['major'], $cl['grade_level'], $id]);
        echo "Updated class: {$cl['class_name']} (ID: $id)\n";
    }
    $classMap[$cl['class_name']] = $id;
}

// 2. TEACHERS
$teachers = [
    ['username' => 'guru_rpl', 'name' => 'Budi Santoso, S.Kom', 'major' => 'RPL', 'nip_nis' => '198501012010011001', 'subjects' => 'Rekayasa Perangkat Lunak'],
    ['username' => 'guru_tkj', 'name' => 'Hendra Pratama, S.T', 'major' => 'TKJ', 'nip_nis' => '198702022011011002', 'subjects' => 'Teknik Komputer dan Jaringan'],
    ['username' => 'guru_dkv', 'name' => 'Citra Lestari, S.Sn', 'major' => 'DKV', 'nip_nis' => '199003032014012003', 'subjects' => 'Desain Komunikasi Visual'],
];

$teacherMap = [];
foreach ($teachers as $t) {
    $st = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $st->execute([$t['username']]);
    $tid = $st->fetchColumn();
    if (!$tid) {
        $ins = $pdo->prepare("INSERT INTO users (username, password, name, role, nip_nis, major, subjects) VALUES (?, ?, ?, 'guru', ?, ?, ?)");
        $ins->execute([$t['username'], $passHash, $t['name'], $t['nip_nis'], $t['major'], $t['subjects']]);
        $tid = $pdo->lastInsertId();
        echo "Created teacher: {$t['name']} ({$t['username']})\n";
    } else {
        $pdo->prepare("UPDATE users SET password = ?, name = ?, major = ?, subjects = ? WHERE id = ?")->execute([$passHash, $t['name'], $t['major'], $t['subjects'], $tid]);
        echo "Updated teacher: {$t['name']} ({$t['username']})\n";
    }
    $teacherMap[$t['username']] = $tid;
}

// 3. STUDENTS
$students = [
    // RPL
    ['username' => 'siswa_rpl12', 'name' => 'Rian Permana (RPL 12)', 'class' => 'XII RPL 1', 'major' => 'RPL', 'nip_nis' => '1024013001'],
    ['username' => 'siswa_rpl10', 'name' => 'Dimas Aditya (RPL 10)', 'class' => 'X RPL 1', 'major' => 'RPL', 'nip_nis' => '1024013002'],
    // TKJ
    ['username' => 'siswa_tkj12', 'name' => 'Tegar Wijaya (TKJ 12)', 'class' => 'XII TKJ 1', 'major' => 'TKJ', 'nip_nis' => '1024014001'],
    ['username' => 'siswa_tkj11', 'name' => 'Bagas Saputra (TKJ 11)', 'class' => 'XI TKJ 1', 'major' => 'TKJ', 'nip_nis' => '1024014002'],
    // DKV
    ['username' => 'siswa_dkv12', 'name' => 'Dina Maharani (DKV 12)', 'class' => 'XII DKV 1', 'major' => 'DKV', 'nip_nis' => '1024015001'],
    ['username' => 'siswa_dkv10', 'name' => 'Siti Nurhaliza (DKV 10)', 'class' => 'X DKV 1', 'major' => 'DKV', 'nip_nis' => '1024015002'],
];

foreach ($students as $s) {
    $cid = $classMap[$s['class']] ?? null;
    $st = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $st->execute([$s['username']]);
    $sid = $st->fetchColumn();
    if (!$sid) {
        $ins = $pdo->prepare("INSERT INTO users (username, password, name, role, nip_nis, class_id, major) VALUES (?, ?, ?, 'siswa', ?, ?, ?)");
        $ins->execute([$s['username'], $passHash, $s['name'], $s['nip_nis'], $cid, $s['major']]);
        echo "Created student: {$s['name']} ({$s['username']}) in class {$s['class']}\n";
    } else {
        $pdo->prepare("UPDATE users SET password = ?, name = ?, class_id = ?, major = ? WHERE id = ?")->execute([$passHash, $s['name'], $cid, $s['major'], $sid]);
        echo "Updated student: {$s['name']} ({$s['username']}) in class {$s['class']}\n";
    }
}

// 4. COURSES
$courses = [
    // RPL 12
    [
        'code' => 'RPL-BD12',
        'name' => 'Pemodelan Perangkat Lunak & Basis Data (Kelas 12 RPL)',
        'class' => 'XII RPL 1',
        'major' => 'RPL',
        'teacher' => 'guru_rpl',
        'icon' => 'fa-database',
        'desc' => 'Arsitektur database enterprise, normalisasi tabel, stored procedure, dan perancangan ERD kelas 12.'
    ],
    // RPL 10
    [
        'code' => 'RPL-DP10',
        'name' => 'Dasar Pemrograman & Logika Algoritma (Kelas 10 RPL)',
        'class' => 'X RPL 1',
        'major' => 'RPL',
        'teacher' => 'guru_rpl',
        'icon' => 'fa-code',
        'desc' => 'Konsep dasar algoritma, flowchart, tipe data, dan sintaks logika pemrograman dasar untuk kelas 10 RPL.'
    ],
    // TKJ 12
    [
        'code' => 'TKJ-AIJ12',
        'name' => 'Administrasi Infrastruktur Jaringan (Kelas 12 TKJ)',
        'class' => 'XII TKJ 1',
        'major' => 'TKJ',
        'teacher' => 'guru_tkj',
        'icon' => 'fa-network-wired',
        'desc' => 'Konfigurasi BGP, VLAN trunking, firewall filter rules, QoS bandwith management MikroTik kelas 12 TKJ.'
    ],
    // TKJ 11
    [
        'code' => 'TKJ-WAN11',
        'name' => 'Teknologi Jaringan Berbasis Luas (Kelas 11 TKJ)',
        'class' => 'XI TKJ 1',
        'major' => 'TKJ',
        'teacher' => 'guru_tkj',
        'icon' => 'fa-server',
        'desc' => 'Jaringan nirkabel, fiber optic, routing statis dan dinamis OSPF kelas 11 TKJ.'
    ],
    // DKV 12
    [
        'code' => 'DKV-DP12',
        'name' => 'Desain Publikasi & Branding Interaktif (Kelas 12 DKV)',
        'class' => 'XII DKV 1',
        'major' => 'DKV',
        'teacher' => 'guru_dkv',
        'icon' => 'fa-palette',
        'desc' => 'Brand identity guidelines, media cetak komersial, motion branding, dan kurasi portofolio kelulusan.'
    ],
    // DKV 10
    [
        'code' => 'DKV-DDG10',
        'name' => 'Dasar Desain Grafis & Tipografi (Kelas 10 DKV)',
        'class' => 'X DKV 1',
        'major' => 'DKV',
        'teacher' => 'guru_dkv',
        'icon' => 'fa-pen-nib',
        'desc' => 'Teori warna, hierarki visual, layout nirmana, dan pengenalan software vektor untuk kelas 10 DKV.'
    ],
];

$courseMap = [];
foreach ($courses as $c) {
    $cid = $classMap[$c['class']] ?? null;
    $tid = $teacherMap[$c['teacher']] ?? 1;

    $st = $pdo->prepare("SELECT id FROM courses WHERE course_code = ?");
    $st->execute([$c['code']]);
    $courseId = $st->fetchColumn();

    if (!$courseId) {
        $ins = $pdo->prepare("INSERT INTO courses (course_code, course_name, class_id, teacher_id, icon, major, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([$c['code'], $c['name'], $cid, $tid, $c['icon'], $c['major'], $c['desc']]);
        $courseId = $pdo->lastInsertId();
        echo "Created course: {$c['name']} (ID: $courseId)\n";
    } else {
        $pdo->prepare("UPDATE courses SET course_name = ?, class_id = ?, teacher_id = ?, icon = ?, major = ?, description = ? WHERE id = ?")
            ->execute([$c['name'], $cid, $tid, $c['icon'], $c['major'], $c['desc'], $courseId]);
        echo "Updated course: {$c['name']} (ID: $courseId)\n";
    }
    $courseMap[$c['code']] = $courseId;
}

// 5. SAMPLE FILES IN UPLOADS
$uploadDir = __DIR__ . '/../uploads/materials/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Sample PDF
$pdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>/Contents 4 0 R>>endobj\n4 0 obj<</Length 55>>stream\nBT /F1 14 Tf 50 700 Td (Modul E-Learning SMKN 1 Cibinong) Tj ET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000216 00000 n \ntrailer<</Size 5/Root 1 0 R>>\nstartxref\n320\n%%EOF";
file_put_contents($uploadDir . 'sample_modul_rpl12.pdf', $pdfContent);
file_put_contents($uploadDir . 'sample_slide_tkj12.pptx', "PK\x03\x04[Sample PowerPoint Presentation Data]");
file_put_contents($uploadDir . 'sample_panduan_dkv12.docx', "PK\x03\x04[Sample Word Document Data]");

// Seed Materials for RPL 12
$rpl12Id = $courseMap['RPL-BD12'];
$pdo->prepare("DELETE FROM course_materials WHERE course_id = ?")->execute([$rpl12Id]);
$pdo->prepare("INSERT INTO course_materials (course_id, title, type, content_url, content, file_name, file_size, order_num, created_by) VALUES
    (?, 'Modul 1: Arsitektur Basis Data Enterprise & Indexing', 'pdf', 'uploads/materials/sample_modul_rpl12.pdf', 'Pelajari konsep B-Tree Indexing dan optimasi query PostgreSQL/MySQL untuk performa jutaan baris.', 'Modul_Arsitektur_BasisData_12RPL.pdf', 1048576, 1, ?),
    (?, 'Slide Presentasi: Perancangan ERD & Normalisasi Database', 'pptx', 'uploads/materials/sample_slide_tkj12.pptx', 'Materi presentasi mengenai tahap normalisasi 1NF sampai 3NF beserta contoh kasus e-commerce.', 'Slide_Normalisasi_Database.pptx', 2560000, 2, ?),
    (?, 'Catatan: Tips Menulis Stored Procedures & Triggers', 'text', NULL, 'Pastikan selalu menggunakan DELIMITER // sebelum membuat trigger atau procedure untuk menghindari syntax error.', NULL, 0, 3, ?)
")->execute([$rpl12Id, $teacherMap['guru_rpl'], $rpl12Id, $teacherMap['guru_rpl'], $rpl12Id, $teacherMap['guru_rpl']]);
echo "Seeded materials for RPL 12.\n";

// Seed Materials for TKJ 12
$tkj12Id = $courseMap['TKJ-AIJ12'];
$pdo->prepare("DELETE FROM course_materials WHERE course_id = ?")->execute([$tkj12Id]);
$pdo->prepare("INSERT INTO course_materials (course_id, title, type, content_url, content, file_name, file_size, order_num, created_by) VALUES
    (?, 'Topologi & Panduan Konfigurasi MikroTik BGP Routing', 'pdf', 'uploads/materials/sample_modul_rpl12.pdf', 'Langkah demi langkah routing BGP antar Autonomous System (AS) di RouterBOARD.', 'Panduan_BGP_Mikrotik_TKJ12.pdf', 1520000, 1, ?),
    (?, 'Dokumen SOP: Keamanan Firewall Jaringan Sekolah', 'word', 'uploads/materials/sample_panduan_dkv12.docx', 'Standar operasional pencegahan serangan DoS/DDoS dan filter port terbuka.', 'SOP_Firewall_Jaringan.docx', 840000, 2, ?)
")->execute([$tkj12Id, $teacherMap['guru_tkj'], $tkj12Id, $teacherMap['guru_tkj']]);
echo "Seeded materials for TKJ 12.\n";

// Seed Materials for DKV 12
$dkv12Id = $courseMap['DKV-DP12'];
$pdo->prepare("DELETE FROM course_materials WHERE course_id = ?")->execute([$dkv12Id]);
$pdo->prepare("INSERT INTO course_materials (course_id, title, type, content_url, content, file_name, file_size, order_num, created_by) VALUES
    (?, 'Brand Identity Guidelines & Style Guide Template', 'pdf', 'uploads/materials/sample_modul_rpl12.pdf', 'Pedoman visual perancangan logo, typography, color palette, dan safe zone.', 'DKV12_Brand_Identity_Guidelines.pdf', 3140000, 1, ?),
    (?, 'Panduan Penyusunan Dokumen Portofolio Kelulusan', 'word', 'uploads/materials/sample_panduan_dkv12.docx', 'Format dan struktur kurasi portofolio desain siswa kelas 12 DKV.', 'Panduan_Portofolio_DKV.docx', 920000, 2, ?)
")->execute([$dkv12Id, $teacherMap['guru_dkv'], $dkv12Id, $teacherMap['guru_dkv']]);
echo "Seeded materials for DKV 12.\n";

// 6. QUIZZES WITH ESSAYS AND MULTIPLE CHOICES
// Quiz for RPL 12
$stQ1 = $pdo->prepare("SELECT id FROM quizzes WHERE title = 'Ujian Pemodelan Basis Data Kelas 12 RPL'");
$stQ1->execute();
$qRplId = $stQ1->fetchColumn();
if (!$qRplId) {
    $insQ = $pdo->prepare("INSERT INTO quizzes (title, description, duration_minutes, created_by, status, course_id, kkm, shuffle_questions, release_date)
        VALUES ('Ujian Pemodelan Basis Data Kelas 12 RPL', 'Ujian komprehensif basis data kelas 12 RPL mencakup normalisasi dan query optimization.', 60, ?, 'active', ?, 75, 0, CURDATE())");
    $insQ->execute([$teacherMap['guru_rpl'], $rpl12Id]);
    $qRplId = $pdo->lastInsertId();
    echo "Created quiz for RPL 12: ID $qRplId\n";

    // Insert PG Question
    $pdo->prepare("INSERT INTO questions (quiz_id, type, question_text, option_a, option_b, option_c, option_d, correct_option, points, explanation) VALUES
        (?, 'pg', 'Perintah SQL manakah yang digunakan untuk membuat indeks unik pada kolom email di tabel users?', 'CREATE INDEX idx_email ON users(email);', 'CREATE UNIQUE INDEX uq_email ON users(email);', 'ALTER TABLE users ADD UNIQUE(email);', 'SELECT UNIQUE email FROM users;', 'B', 20, 'CREATE UNIQUE INDEX uq_email ON users(email) merupakan sintaks DDL standar yang secara eksplisit membuat indeks unik.')")
        ->execute([$qRplId]);

    // Insert Essay Question
    $pdo->prepare("INSERT INTO questions (quiz_id, type, question_text, points, explanation) VALUES
        (?, 'essay', 'Jelaskan perbedaan mendasar antara First Normal Form (1NF), Second Normal Form (2NF), dan Third Normal Form (3NF) dalam perancangan basis data relational, serta berikan satu contoh penerapannya!', 80, 'Kriteria Penilaian Essay: 1. Penjelasan 1NF: Nilai atribut harus atomik (tidak ada repeating groups). 2. Penjelasan 2NF: Memenuhi 1NF dan tidak ada ketergantungan parsial pada primary key komposit. 3. Penjelasan 3NF: Memenuhi 2NF dan tidak ada ketergantungan transitif. 4. Contoh kasus konkret.')")
        ->execute([$qRplId]);
}

// Quiz for TKJ 12
$stQ2 = $pdo->prepare("SELECT id FROM quizzes WHERE title = 'Ujian Routing BGP & Firewall Kelas 12 TKJ'");
$stQ2->execute();
$qTkjId = $stQ2->fetchColumn();
if (!$qTkjId) {
    $insQ = $pdo->prepare("INSERT INTO quizzes (title, description, duration_minutes, created_by, status, course_id, kkm, shuffle_questions, release_date)
        VALUES ('Ujian Routing BGP & Firewall Kelas 12 TKJ', 'Evaluasi konfigurasi routing BGP dan keamanan jaringan MikroTik kelas 12 TKJ.', 60, ?, 'active', ?, 75, 0, CURDATE())");
    $insQ->execute([$teacherMap['guru_tkj'], $tkj12Id]);
    $qTkjId = $pdo->lastInsertId();
    echo "Created quiz for TKJ 12: ID $qTkjId\n";

    $pdo->prepare("INSERT INTO questions (quiz_id, type, question_text, option_a, option_b, option_c, option_d, correct_option, points, explanation) VALUES
        (?, 'pg', 'Port TCP default yang digunakan oleh protokol BGP untuk komunikasi peering adalah?', 'Port 179', 'Port 80', 'Port 443', 'Port 22', 'A', 25, 'Protokol BGP (Border Gateway Protocol) bekerja pada transport layer menggunakan TCP port 179 untuk pertukaran rute.')")
        ->execute([$qTkjId]);

    $pdo->prepare("INSERT INTO questions (quiz_id, type, question_text, points, explanation) VALUES
        (?, 'essay', 'Uraikan bagaimana mekanisme Firewall Filter Rules pada MikroTik bekerja membedakan traffic input, forward, dan output serta mengapa pengaturan chain forward sangat krusial dalam keamanan jaringan!', 75, 'Rubrik Evaluasi Essay: Chain input untuk traffic menuju router itu sendiri, chain forward untuk traffic yang melintasi router dari klien ke internet/jaringan lain, chain output untuk traffic yang berasal dari router menuju luar.')")
        ->execute([$qTkjId]);
}

echo "=== SEEDING COMPLETED SUCCESSFULLY ===\n";

