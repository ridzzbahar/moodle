<?php
// cbt/scratch/replace_all_emojis.php
$file = 'c:/xampp/htdocs/moodle/cbt/js/app.js';
$code = file_get_contents($file);

// Replace mapping for specific known UI patterns
$replacements = [
    // Theme toggle
    "t === 'dark' ? '🌙' : '☀️';" => "t === 'dark' ? '<i class=\"fa-solid fa-moon\"></i>' : '<i class=\"fa-solid fa-sun\"></i>';",
    "eyeIcon.textContent = '🙈';" => "eyeIcon.innerHTML = '<i class=\"fa-solid fa-eye-slash\"></i>';",
    "eyeIcon.textContent = '👁️';" => "eyeIcon.innerHTML = '<i class=\"fa-solid fa-eye\"></i>';",
    "eyeIcon.textContent = '👁';" => "eyeIcon.innerHTML = '<i class=\"fa-solid fa-eye\"></i>';",
    "<span id=\"eye-icon\">👁️</span>" => "<span id=\"eye-icon\"><i class=\"fa-solid fa-eye\"></i></span>",
    "<span id=\"eye-icon\">👁</span>" => "<span id=\"eye-icon\"><i class=\"fa-solid fa-eye\"></i></span>",

    // Login screen
    '<span class="ssl-badge">🔒 Secure Connection SSL/TLS</span>' => '<span class="ssl-badge"><i class="fa-solid fa-lock"></i> Secure Connection SSL/TLS</span>',
    '<span class="role-icon">🎓</span>' => '<span class="role-icon"><i class="fa-solid fa-graduation-cap"></i></span>',
    '<span class="role-icon">👨‍🏫</span>' => '<span class="role-icon"><i class="fa-solid fa-chalkboard-user"></i></span>',
    '<span class="role-icon">🛡️</span>' => '<span class="role-icon"><i class="fa-solid fa-shield-halved"></i></span>',
    '<span class="role-icon">🛡</span>' => '<span class="role-icon"><i class="fa-solid fa-shield-halved"></i></span>',
    '<span class="input-icon">👤</span>' => '<span class="input-icon"><i class="fa-solid fa-user"></i></span>',
    '<span class="input-icon">🔒</span>' => '<span class="input-icon"><i class="fa-solid fa-lock"></i></span>',
    '🔐 Masuk / Login' => '<i class="fa-solid fa-right-to-bracket"></i> Masuk / Login',
    '<button class="demo-btn" onclick="App.quickLogin(\'admin\',\'password123\')">🛡️ Admin</button>' => '<button class="demo-btn" onclick="App.quickLogin(\'admin\',\'password123\')"><i class="fa-solid fa-shield-halved"></i> Admin</button>',
    '<button class="demo-btn" onclick="App.quickLogin(\'guru1\',\'password123\')">👨‍🏫 Bpk. Andi (Guru)</button>' => '<button class="demo-btn" onclick="App.quickLogin(\'guru1\',\'password123\')"><i class="fa-solid fa-chalkboard-user"></i> Bpk. Andi (Guru)</button>',
    '<button class="demo-btn" onclick="App.quickLogin(\'siswa1\',\'password123\')">🎓 Ahmad (Siswa)</button>' => '<button class="demo-btn" onclick="App.quickLogin(\'siswa1\',\'password123\')"><i class="fa-solid fa-graduation-cap"></i> Ahmad (Siswa)</button>',
    '<button class="demo-btn" onclick="App.quickLogin(\'siswa2\',\'password123\')">🎓 Bunga (Siswa)</button>' => '<button class="demo-btn" onclick="App.quickLogin(\'siswa2\',\'password123\')"><i class="fa-solid fa-graduation-cap"></i> Bunga (Siswa)</button>',
    "btn.textContent = '🔐 Masuk ke Portal Akademik';" => "btn.innerHTML = '<i class=\"fa-solid fa-right-to-bracket\"></i> Masuk ke Portal Akademik';",
    
    // Navbar user
    '<span class="nav-user-name">${this.escHtml(u.name)} ⚙️</span>' => '<span class="nav-user-name">${this.escHtml(u.name)} <i class="fa-solid fa-gear" style="font-size:0.8rem;opacity:0.75;"></i></span>',
    '<span class="nav-user-name">${this.escHtml(u.name)} ⚙</span>' => '<span class="nav-user-name">${this.escHtml(u.name)} <i class="fa-solid fa-gear" style="font-size:0.8rem;opacity:0.75;"></i></span>',

    // Role mappings
    "const roleEmoji = { admin: '🛡️', guru: '👨‍🏫', siswa: '🎓' };" => "const roleEmoji = { admin: '<i class=\"fa-solid fa-shield-halved\"></i>', guru: '<i class=\"fa-solid fa-chalkboard-user\"></i>', siswa: '<i class=\"fa-solid fa-graduation-cap\"></i>' };",
    "const roleMap = { admin: '🛡️ Admin', guru: '👨‍🏫 Guru', siswa: '🎓 Siswa' };" => "const roleMap = { admin: '<i class=\"fa-solid fa-shield-halved\"></i> Admin', guru: '<i class=\"fa-solid fa-chalkboard-user\"></i> Guru', siswa: '<i class=\"fa-solid fa-graduation-cap\"></i> Siswa' };",
    '<option value="siswa">🎓 Siswa</option>' => '<option value="siswa">Siswa</option>',
    '<option value="guru">👨‍🏫 Guru</option>' => '<option value="guru">Guru</option>',
    '<option value="admin">🛡️ Admin</option>' => '<option value="admin">Admin</option>',

    // Dashboard
    "! 👋</div>" => "!</div>",
    '<div class="card-title">📋 Rekap Presensi Saya</div>' => '<div class="card-title"><i class="fa-solid fa-clipboard-check"></i> Rekap Presensi Saya</div>',
    '<div class="card-title">📢 ${this.t(\'announcements\')}' => '<div class="card-title"><i class="fa-solid fa-bullhorn"></i> ${this.t(\'announcements\')}',
    '<div class="card-title">⚡ Menu Pintas</div>' => '<div class="card-title"><i class="fa-solid fa-bolt"></i> Menu Pintas</div>',
    '<button class="btn btn-primary btn-full" onclick="App.renderAppLayout(\'cbt\')">📝 Buka Menu Ujian CBT</button>' => '<button class="btn btn-primary btn-full" onclick="App.renderAppLayout(\'cbt\')"><i class="fa-solid fa-file-pen"></i> Buka Menu Ujian CBT</button>',
    '<button class="btn btn-outline btn-full"  onclick="App.renderAppLayout(\'rekap\')">📊 Rekap Absensi Siswa</button>' => '<button class="btn btn-outline btn-full"  onclick="App.renderAppLayout(\'rekap\')"><i class="fa-solid fa-chart-simple"></i> Rekap Absensi Siswa</button>',
    '<button class="btn btn-outline btn-full" onclick="App.renderAppLayout(\'grades\')">🏆 Lihat Daftar Nilai</button>' => '<button class="btn btn-outline btn-full" onclick="App.renderAppLayout(\'grades\')"><i class="fa-solid fa-trophy"></i> Lihat Daftar Nilai</button>',
    '${isStaff ? `<button class="btn btn-primary btn-full" onclick="App.renderAppLayout(\'attendance\')">📋 Input Presensi Hari Ini</button>` : \'\'}' => '${isStaff ? `<button class="btn btn-primary btn-full" onclick="App.renderAppLayout(\'attendance\')"><i class="fa-solid fa-clipboard-user"></i> Input Presensi Hari Ini</button>` : \'\'}',
    '<div class="card-title" style="font-size:0.9rem;">📝 Ujian Terbaru</div>' => '<div class="card-title" style="font-size:0.9rem;"><i class="fa-solid fa-file-pen"></i> Ujian Terbaru</div>',

    // Empty states
    '<div class="empty-icon">📝</div>' => '<div class="empty-icon"><i class="fa-solid fa-file-pen"></i></div>',
    '<div class="empty-icon">📢</div>' => '<div class="empty-icon"><i class="fa-solid fa-bullhorn"></i></div>',
    '<div class="empty-icon">📚</div>' => '<div class="empty-icon"><i class="fa-solid fa-book"></i></div>',
    '<div class="empty-icon">🏆</div>' => '<div class="empty-icon"><i class="fa-solid fa-trophy"></i></div>',
    '<div class="empty-icon">📭</div>' => '<div class="empty-icon"><i class="fa-solid fa-inbox"></i></div>',
    '<div class="empty-icon">📋</div>' => '<div class="empty-icon"><i class="fa-solid fa-clipboard-list"></i></div>',
    '<div class="empty-icon">👤</div>' => '<div class="empty-icon"><i class="fa-solid fa-user"></i></div>',
    '<div class="empty-icon">⚠️</div>' => '<div class="empty-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>',
    '<div class="empty-icon">⚠</div>' => '<div class="empty-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>',
    '<div class="empty-icon">📁</div>' => '<div class="empty-icon"><i class="fa-solid fa-folder-open"></i></div>',
    '<div class="empty-icon">💬</div>' => '<div class="empty-icon"><i class="fa-solid fa-comments"></i></div>',

    // Mapel / Course cards
    '<span style="font-size:2rem;">${c.icon || \'📚\'}</span>' => '<span style="font-size:2rem;"><i class="fa-solid fa-book-bookmark" style="color:var(--cyan);"></i></span>',
    '🏛️ Kelas:' => '<i class="fa-solid fa-building-columns"></i> Kelas:',
    '🏛 Kelas:' => '<i class="fa-solid fa-building-columns"></i> Kelas:',
    '👨‍🏫 Pengampu:' => '<i class="fa-solid fa-chalkboard-user"></i> Pengampu:',
    '👨 🏫 Pengampu:' => '<i class="fa-solid fa-chalkboard-user"></i> Pengampu:',
    '📁 <b>${c.material_count || 0}</b> Materi &bull; 📝 <b>${c.assignment_count || 0}</b> Tugas &bull; 🎯 <b>${c.quiz_count || 0}</b> Ujian' =>
    '<i class="fa-solid fa-folder"></i> <b>${c.material_count || 0}</b> Materi &bull; <i class="fa-solid fa-file-pen"></i> <b>${c.assignment_count || 0}</b> Tugas &bull; <i class="fa-solid fa-bullseye"></i> <b>${c.quiz_count || 0}</b> Ujian',
    '<button class="btn btn-primary btn-full" onclick="App.openCourseDetail(${c.id})">📖 Masuk Kelas & Materi</button>' => '<button class="btn btn-primary btn-full" onclick="App.openCourseDetail(${c.id})"><i class="fa-solid fa-book-open"></i> Masuk Kelas & Materi</button>',
    '<button class="btn btn-outline" style="flex:1;" onclick="App.renderAppLayout(\'cbt\')">📝 CBT (${c.quiz_count || 0})</button>' => '<button class="btn btn-outline" style="flex:1;" onclick="App.renderAppLayout(\'cbt\')"><i class="fa-solid fa-file-pen"></i> CBT (${c.quiz_count || 0})</button>',
    'title="Edit Mapel">✏️ Edit</button>' => 'title="Edit Mapel"><i class="fa-solid fa-pen"></i> Edit</button>',
    'title="Edit Mapel">✏ Edit</button>' => 'title="Edit Mapel"><i class="fa-solid fa-pen"></i> Edit</button>',
    'title="Hapus Mapel">🗑️</button>' => 'title="Hapus Mapel"><i class="fa-solid fa-trash"></i></button>',
    'title="Hapus Mapel">🗑</button>' => 'title="Hapus Mapel"><i class="fa-solid fa-trash"></i></button>',

    // Course Detail tabs
    '📁 Materi Pembelajaran' => '<i class="fa-solid fa-folder"></i> Materi Pembelajaran',
    '📝 Tugas Terstruktur' => '<i class="fa-solid fa-file-pen"></i> Tugas Terstruktur',
    '💬 Forum Diskusi' => '<i class="fa-solid fa-comments"></i> Forum Diskusi',
    '🎯 Kuis / CBT Terkait' => '<i class="fa-solid fa-bullseye"></i> Kuis / CBT Terkait',
    '➕ Tambah Materi Baru' => '<i class="fa-solid fa-plus"></i> Tambah Materi Baru',
    '➕ Berikan Tugas Baru' => '<i class="fa-solid fa-plus"></i> Berikan Tugas Baru',
    '💬 Kirim Diskusi Baru' => '<i class="fa-solid fa-paper-plane"></i> Kirim Diskusi Baru',
    '📤 Kumpulkan Jawaban Tugas' => '<i class="fa-solid fa-upload"></i> Kumpulkan Jawaban Tugas',
    '🔗 Buka Tautan Eksternal' => '<i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Tautan Eksternal',
    '📥 Unduh Dokumen' => '<i class="fa-solid fa-download"></i> Unduh Dokumen',

    // Stat boxes in dashboard
    '<div class="stat-icon-box">📚</div>' => '<div class="stat-icon-box"><i class="fa-solid fa-book"></i></div>',
    '<div class="stat-icon-box" style="background:rgba(99,102,241,0.15);font-size:1.3rem;">🏛️</div>' => '<div class="stat-icon-box" style="background:rgba(99,102,241,0.15);font-size:1.3rem;"><i class="fa-solid fa-building-columns" style="color:#6366f1;"></i></div>',
    '<div class="stat-icon-box" style="background:rgba(99,102,241,0.15);font-size:1.3rem;">🏛</div>' => '<div class="stat-icon-box" style="background:rgba(99,102,241,0.15);font-size:1.3rem;"><i class="fa-solid fa-building-columns" style="color:#6366f1;"></i></div>',
    '<div class="stat-icon-box" style="background:var(--emerald-dim);font-size:1.3rem;">🎓</div>' => '<div class="stat-icon-box" style="background:var(--emerald-dim);font-size:1.3rem;"><i class="fa-solid fa-graduation-cap" style="color:var(--emerald);"></i></div>',
    '<div class="stat-icon-box" style="background:var(--amber-dim);font-size:1.3rem;">📝</div>' => '<div class="stat-icon-box" style="background:var(--amber-dim);font-size:1.3rem;"><i class="fa-solid fa-file-pen" style="color:var(--amber);"></i></div>',
    '<div class="stat-icon-box" style="background:var(--cyan-dim);font-size:1.3rem;">✅</div>' => '<div class="stat-icon-box" style="background:var(--cyan-dim);font-size:1.3rem;"><i class="fa-solid fa-circle-check" style="color:var(--cyan);"></i></div>',
    '<div class="stat-icon-box">📝</div>' => '<div class="stat-icon-box"><i class="fa-solid fa-file-pen"></i></div>',
    '<div class="stat-icon-box" style="background:var(--emerald-dim);">✅</div>' => '<div class="stat-icon-box" style="background:var(--emerald-dim);"><i class="fa-solid fa-circle-check" style="color:var(--emerald);"></i></div>',
    '<div class="stat-icon-box" style="background:var(--danger-dim);">❌</div>' => '<div class="stat-icon-box" style="background:var(--danger-dim);"><i class="fa-solid fa-circle-xmark" style="color:var(--danger);"></i></div>',
    '<div class="stat-icon-box" style="background:var(--emerald-dim);font-size:1.3rem;">✅</div>' => '<div class="stat-icon-box" style="background:var(--emerald-dim);font-size:1.3rem;"><i class="fa-solid fa-circle-check" style="color:var(--emerald);"></i></div>',
    '<div class="stat-icon-box" style="background:var(--blue-dim);font-size:1.3rem;">📄</div>' => '<div class="stat-icon-box" style="background:var(--blue-dim);font-size:1.3rem;"><i class="fa-solid fa-file-lines" style="color:#3b82f6;"></i></div>',
    '<div class="stat-icon-box" style="background:var(--amber-dim);font-size:1.3rem;">🤒</div>' => '<div class="stat-icon-box" style="background:var(--amber-dim);font-size:1.3rem;"><i class="fa-solid fa-hospital-user" style="color:var(--amber);"></i></div>',
    '<div class="stat-icon-box" style="background:var(--danger-dim);font-size:1.3rem;">❌</div>' => '<div class="stat-icon-box" style="background:var(--danger-dim);font-size:1.3rem;"><i class="fa-solid fa-circle-xmark" style="color:var(--danger);"></i></div>',

    // Announcements
    '<div class="section-title">📢 Pengumuman & Informasi Sekolah</div>' => '<div class="section-title"><i class="fa-solid fa-bullhorn"></i> Pengumuman & Informasi Sekolah</div>',
    '${isStaff ? `<button class="btn btn-primary" onclick="App.showAnnForm()">➕ Buat Pengumuman</button>` : \'\'}' => '${isStaff ? `<button class="btn btn-primary" onclick="App.showAnnForm()"><i class="fa-solid fa-plus"></i> Buat Pengumuman</button>` : \'\'}',
    '🌐 Semua Target' => '<i class="fa-solid fa-globe"></i> Semua Target',
    '🎓 Siswa' => '<i class="fa-solid fa-graduation-cap"></i> Siswa',
    '👨‍🏫 Guru' => '<i class="fa-solid fa-chalkboard-user"></i> Guru',
    '📌 Disematkan' => '<i class="fa-solid fa-thumbtack"></i> Disematkan',
    '📌 Sematkan Pengumuman (Pin to top)' => '<i class="fa-solid fa-thumbtack"></i> Sematkan Pengumuman (Pin to top)',
    '🎯 Target:' => '<i class="fa-solid fa-bullseye"></i> Target:',
    'title="Edit Pengumuman">✏️ Edit</button>' => 'title="Edit Pengumuman"><i class="fa-solid fa-pen"></i> Edit</button>',
    'title="Edit Pengumuman">✏ Edit</button>' => 'title="Edit Pengumuman"><i class="fa-solid fa-pen"></i> Edit</button>',
    'title="Hapus Pengumuman">🗑️ Hapus</button>' => 'title="Hapus Pengumuman"><i class="fa-solid fa-trash"></i> Hapus</button>',
    'title="Hapus Pengumuman">🗑 Hapus</button>' => 'title="Hapus Pengumuman"><i class="fa-solid fa-trash"></i> Hapus</button>',
    '✍️ Oleh:' => '<i class="fa-solid fa-pen-fancy"></i> Oleh:',
    '✍ Oleh:' => '<i class="fa-solid fa-pen-fancy"></i> Oleh:',
    '📅 ' => '<i class="fa-solid fa-calendar-days"></i> ',

    // CBT & Quizzes
    '🚀 Mulai Ujian' => '<i class="fa-solid fa-rocket"></i> Mulai Ujian',
    '▶️ Lanjutkan Ujian' => '<i class="fa-solid fa-play"></i> Lanjutkan Ujian',
    '▶ Lanjutkan Ujian' => '<i class="fa-solid fa-play"></i> Lanjutkan Ujian',
    '👁️ Lihat Hasil' => '<i class="fa-solid fa-square-poll-vertical"></i> Lihat Hasil',
    '👁 Lihat Hasil' => '<i class="fa-solid fa-square-poll-vertical"></i> Lihat Hasil',
    '🚫 Didiskualifikasi' => '<i class="fa-solid fa-ban"></i> Didiskualifikasi',
    '🚀 Saya Paham, Mulai Ujian' => '<i class="fa-solid fa-rocket"></i> Saya Paham, Mulai Ujian',
    '➕ Tambah Paket Ujian' => '<i class="fa-solid fa-plus"></i> Tambah Paket Ujian',
    '➕ Tambah Soal' => '<i class="fa-solid fa-plus"></i> Tambah Soal',
    '📝 Kelola Soal' => '<i class="fa-solid fa-list-check"></i> Kelola Soal',
    '📊 Hasil' => '<i class="fa-solid fa-chart-column"></i> Hasil',
    '🎥 Live Monitor' => '<i class="fa-solid fa-video"></i> Live Monitor',
    '📹 Proctor Live Monitor' => '<i class="fa-solid fa-video"></i> Proctor Live Monitor',
    '🔄 Refresh Data' => '<i class="fa-solid fa-rotate"></i> Refresh Data',
    '🔄 Refresh Kamera' => '<i class="fa-solid fa-rotate"></i> Refresh Kamera',
    '🔄 Coba Lagi' => '<i class="fa-solid fa-rotate"></i> Coba Lagi',
    '🔄 Reset Ujian' => '<i class="fa-solid fa-rotate-left"></i> Reset Ujian',

    // Attendance
    '<button class="btn btn-secondary" onclick="App.printRekap()">🖨️ Cetak PDF</button>' => '<button class="btn btn-secondary" onclick="App.printRekap()"><i class="fa-solid fa-print"></i> Cetak PDF</button>',
    '<button class="btn btn-secondary" onclick="App.printRekap()">🖨 Cetak PDF</button>' => '<button class="btn btn-secondary" onclick="App.printRekap()"><i class="fa-solid fa-print"></i> Cetak PDF</button>',
    '<button class="btn btn-success" onclick="App.exportExcel()">📥 Export Excel (.xlsx)</button>' => '<button class="btn btn-success" onclick="App.exportExcel()"><i class="fa-solid fa-file-excel"></i> Export Excel (.xlsx)</button>',
    '<button class="btn btn-success" onclick="App.exportExcel()">📥 Export Excel</button>' => '<button class="btn btn-success" onclick="App.exportExcel()"><i class="fa-solid fa-file-excel"></i> Export Excel</button>',
    '📋 Tabel Rekap Kehadiran Siswa' => '<i class="fa-solid fa-clipboard-list"></i> Tabel Rekap Kehadiran Siswa',
    '<div class="hero-badge">📋 Presensi Harian & Mata Pelajaran</div>' => '<div class="hero-badge"><i class="fa-solid fa-clipboard-user"></i> Presensi Harian & Mata Pelajaran</div>',
    '💾 Simpan Presensi' => '<i class="fa-solid fa-floppy-disk"></i> Simpan Presensi',
    '👥 Total:' => '<i class="fa-solid fa-users"></i> Total:',
    '🟢 Hadir:' => '<i class="fa-solid fa-circle" style="color:#10b981;font-size:0.75rem;"></i> Hadir:',
    '🔵 Izin:' => '<i class="fa-solid fa-circle" style="color:#3b82f6;font-size:0.75rem;"></i> Izin:',
    '🟡 Sakit:' => '<i class="fa-solid fa-circle" style="color:#f59e0b;font-size:0.75rem;"></i> Sakit:',
    '🔴 Alpa:' => '<i class="fa-solid fa-circle" style="color:#ef4444;font-size:0.75rem;"></i> Alpa:',

    // Grades
    '<div class="section-title">🏆 Daftar Nilai Ujian CBT</div>' => '<div class="section-title"><i class="fa-solid fa-trophy"></i> Daftar Nilai Ujian CBT</div>',
    '<div class="hero-badge">🏆 Penilaian Akademik</div>' => '<div class="hero-badge"><i class="fa-solid fa-trophy"></i> Penilaian Akademik</div>',
    '📥 ${this.t(\'export_csv\')}' => '<i class="fa-solid fa-file-csv"></i> ${this.t(\'export_csv\')}',

    // Users
    '<div class="section-title">👥 Manajemen Pengguna</div>' => '<div class="section-title"><i class="fa-solid fa-users"></i> Manajemen Pengguna</div>',
    '<button class="btn btn-primary" onclick="App.openAddUserModal()">➕ Tambah Pengguna</button>' => '<button class="btn btn-primary" onclick="App.openAddUserModal()"><i class="fa-solid fa-user-plus"></i> Tambah Pengguna</button>',
    '🗑️</button>' => '<i class="fa-solid fa-trash"></i></button>',
    '🗑</button>' => '<i class="fa-solid fa-trash"></i></button>',
    '✏️</button>' => '<i class="fa-solid fa-pen"></i></button>',
    '✏</button>' => '<i class="fa-solid fa-pen"></i></button>',
    '🔍 Cari siswa...' => 'Cari siswa...',
];

foreach ($replacements as $search => $replace) {
    $code = str_replace($search, $replace, $code);
}

// Also replace generic toast emojis: '✅ ', '❌ ', '⚠️ '
$code = preg_replace("/_toast\(\s*'✅\s*/", "_toast('", $code);
$code = preg_replace("/_toast\(\s*`✅\s*/", "_toast(`", $code);
$code = preg_replace("/_toast\(\s*'❌\s*/", "_toast('", $code);
$code = preg_replace("/_toast\(\s*'⚠️\s*/", "_toast('", $code);

file_put_contents($file, $code);
echo "Specific UI emojis replaced successfully.\n";

