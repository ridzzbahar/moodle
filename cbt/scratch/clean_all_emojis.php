<?php
// cbt/scratch/clean_all_emojis.php
$file = 'c:/xampp/htdocs/moodle/cbt/js/app.js';
$code = file_get_contents($file);

$map = [
    '<div style="width:36px;height:36px;border-radius:var(--r-md);background:var(--cyan-dim);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">📝</div>' =>
    '<div style="width:36px;height:36px;border-radius:var(--r-md);background:var(--cyan-dim);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;"><i class="fa-solid fa-file-pen"></i></div>',

    '<div class="hero-title">Selamat Datang, <span>${this.escHtml(u.name.split(\' \')[0])}!</span> 👋</div>' =>
    '<div class="hero-title">Selamat Datang, <span>${this.escHtml(u.name.split(\' \')[0])}!</span></div>',

    "\${a.is_pinned ? '📌 ' : ''}" =>
    "\${a.is_pinned ? '<i class=\"fa-solid fa-thumbtack\"></i> ' : ''}",

    '<div class="section-title">📚 Mata Pelajaran</div>' =>
    '<div class="section-title"><i class="fa-solid fa-book"></i> Mata Pelajaran</div>',

    '${isStaff ? `<button class="btn btn-primary" onclick="App.openAddCourseModal()">➕ Tambah Mapel</button>` : \'\'}' =>
    '${isStaff ? `<button class="btn btn-primary" onclick="App.openAddCourseModal()"><i class="fa-solid fa-plus"></i> Tambah Mapel</button>` : \'\'}',

    "\${m === 'all' ? '🌐 Semua Jurusan' : '🎯 Jurusan ' + m}" =>
    "\${m === 'all' ? '<i class=\"fa-solid fa-globe\"></i> Semua Jurusan' : '<i class=\"fa-solid fa-bullseye\"></i> Jurusan ' + m}",

    '<label class="form-label">Ikon (Emoji)</label><input type="text" id="c-icon" class="form-control" value="📚" maxlength="8">' =>
    '<label class="form-label">Kode Ikon FontAwesome</label><input type="text" id="c-icon" class="form-control" value="fa-book" maxlength="30" placeholder="contoh: fa-book, fa-laptop-code">',

    '<input type="text" id="ec-icon" class="form-control" value="${this.escHtml(course.icon || \'📚\')}" maxlength="8">' =>
    '<input type="text" id="ec-icon" class="form-control" value="${this.escHtml(course.icon || \'fa-book\')}" maxlength="30">',

    '<button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>' =>
    '<button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>',

    '<div style="font-size:2.8rem; margin-right:1rem;">${c.icon || \'📚\'}</div>' =>
    '<div style="font-size:2.8rem; margin-right:1rem; color:var(--cyan);"><i class="fa-solid ${c.icon && c.icon.startsWith(\'fa-\') ? c.icon : \'fa-book\'}"></i></div>',

    '📁 ${stats.materials || 0} Materi &bull; 📝 ${stats.assignments || 0} Tugas &bull; 💬 ${stats.discussions || 0} Diskusi' =>
    '<i class="fa-solid fa-folder"></i> ${stats.materials || 0} Materi &bull; <i class="fa-solid fa-file-pen"></i> ${stats.assignments || 0} Tugas &bull; <i class="fa-solid fa-comments"></i> ${stats.discussions || 0} Diskusi',

    '<button class="btn btn-sm ${activeTab === \'materials\' ? \'btn-primary\' : \'btn-secondary\'}" onclick="App.openCourseDetail(${courseId}, \'materials\')">📁 Materi (${stats.materials || 0})</button>' =>
    '<button class="btn btn-sm ${activeTab === \'materials\' ? \'btn-primary\' : \'btn-secondary\'}" onclick="App.openCourseDetail(${courseId}, \'materials\')"><i class="fa-solid fa-folder"></i> Materi (${stats.materials || 0})</button>',

    '<button class="btn btn-sm ${activeTab === \'assignments\' ? \'btn-primary\' : \'btn-secondary\'}" onclick="App.openCourseDetail(${courseId}, \'assignments\')">📝 Tugas (${stats.assignments || 0})</button>' =>
    '<button class="btn btn-sm ${activeTab === \'assignments\' ? \'btn-primary\' : \'btn-secondary\'}" onclick="App.openCourseDetail(${courseId}, \'assignments\')"><i class="fa-solid fa-file-pen"></i> Tugas (${stats.assignments || 0})</button>',

    '<button class="btn btn-sm ${activeTab === \'discussions\' ? \'btn-primary\' : \'btn-secondary\'}" onclick="App.openCourseDetail(${courseId}, \'discussions\')">💬 Diskusi (${stats.discussions || 0})</button>' =>
    '<button class="btn btn-sm ${activeTab === \'discussions\' ? \'btn-primary\' : \'btn-secondary\'}" onclick="App.openCourseDetail(${courseId}, \'discussions\')"><i class="fa-solid fa-comments"></i> Diskusi (${stats.discussions || 0})</button>',

    "const typeIcon = { pdf: '📄', video: '🎥', link: '🔗', text: '📝' };" =>
    "const typeIcon = { pdf: '<i class=\"fa-solid fa-file-pdf\" style=\"color:#ef4444;\"></i>', video: '<i class=\"fa-solid fa-video\" style=\"color:#3b82f6;\"></i>', link: '<i class=\"fa-solid fa-link\" style=\"color:#10b981;\"></i>', text: '<i class=\"fa-solid fa-file-lines\" style=\"color:#f59e0b;\"></i>' };",

    '<div style="font-size:2rem;">${typeIcon[m.type] || \'📄\'}</div>' =>
    '<div style="font-size:2rem;">${typeIcon[m.type] || \'<i class=\"fa-solid fa-file-lines\"></i>\'}</div>',

    '${m.content_url ? `<a href="${this.escHtml(m.content_url)}" target="_blank" class="btn btn-primary btn-sm">🔗 Buka / Unduh</a>` : \'\'}' =>
    '${m.content_url ? `<a href="${this.escHtml(m.content_url)}" target="_blank" class="btn btn-primary btn-sm"><i class="fa-solid fa-arrow-up-right-from-square"></i> Buka / Unduh</a>` : \'\'}',

    '<div class="section-title" style="font-size:1.1rem;">📁 Modul & Materi Pembelajaran</div>' =>
    '<div class="section-title" style="font-size:1.1rem;"><i class="fa-solid fa-folder-open"></i> Modul & Materi Pembelajaran</div>',

    '${isStaff ? `<button class="btn btn-primary btn-sm" onclick="App._openAddMaterialModal(${courseId})">➕ Upload Materi</button>` : \'\'}' =>
    '${isStaff ? `<button class="btn btn-primary btn-sm" onclick="App._openAddMaterialModal(${courseId})"><i class="fa-solid fa-plus"></i> Upload Materi</button>` : \'\'}',

    '${!isStaff ? `<button class="btn btn-primary btn-sm" onclick="App.openSubmitAssignmentModal(${a.id}, ${courseId})">📤 Kirim / Upload Tugas</button>` : \'\'}' =>
    '${!isStaff ? `<button class="btn btn-primary btn-sm" onclick="App.openSubmitAssignmentModal(${a.id}, ${courseId})"><i class="fa-solid fa-upload"></i> Kirim / Upload Tugas</button>` : \'\'}',

    '<div class="section-title" style="font-size:1.1rem;">📝 Tugas & Pekerjaan Siswa</div>' =>
    '<div class="section-title" style="font-size:1.1rem;"><i class="fa-solid fa-file-pen"></i> Tugas & Pekerjaan Siswa</div>',

    '${isStaff ? `<button class="btn btn-primary btn-sm" onclick="App.openAddAssignmentModal(${courseId})">➕ Buat Tugas Baru</button>` : \'\'}' =>
    '${isStaff ? `<button class="btn btn-primary btn-sm" onclick="App.openAddAssignmentModal(${courseId})"><i class="fa-solid fa-plus"></i> Buat Tugas Baru</button>` : \'\'}',

    '<button type="submit" class="btn btn-primary">📤 Kirim Tugas</button>' =>
    '<button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Kirim Tugas</button>',

    '<div style="font-weight:600;margin-bottom:0.5rem;font-size:0.92rem;">💬 Kirim Pesan Diskusi Kelas</div>' =>
    '<div style="font-weight:600;margin-bottom:0.5rem;font-size:0.92rem;"><i class="fa-solid fa-comments"></i> Kirim Pesan Diskusi Kelas</div>',

    '<button class="btn btn-primary" onclick="App._sendDiscussion(${courseId})">Kirim 🚀</button>' =>
    '<button class="btn btn-primary" onclick="App._sendDiscussion(${courseId})"><i class="fa-solid fa-paper-plane"></i> Kirim</button>',

    '<tr><td style="font-weight:600;">Guru Pengampu</td><td>👨‍🏫 ${this.escHtml(c.teacher_name || \'Belum ditugaskan\')}</td></tr>' =>
    '<tr><td style="font-weight:600;">Guru Pengampu</td><td><i class="fa-solid fa-chalkboard-user"></i> ${this.escHtml(c.teacher_name || \'Belum ditugaskan\')}</td></tr>',

    '<tr><td style="font-weight:600;">Total Evaluasi CBT</td><td>📝 ${stats.quizzes || 0} Paket Ujian Aktif</td></tr>' =>
    '<tr><td style="font-weight:600;">Total Evaluasi CBT</td><td><i class="fa-solid fa-file-pen"></i> ${stats.quizzes || 0} Paket Ujian Aktif</td></tr>',

    '<button class="btn btn-primary" onclick="App.renderAppLayout(\'cbt\')">📝 Buka Ujian CBT Mapel Ini</button>' =>
    '<button class="btn btn-primary" onclick="App.renderAppLayout(\'cbt\')"><i class="fa-solid fa-file-pen"></i> Buka Ujian CBT Mapel Ini</button>',

    '<td>${isStaff ? `<button class="btn btn-danger btn-sm" onclick="App.deleteClass(${cl.id})">🗑️ Hapus</button>` : \'—\'}</td>' =>
    '<td>${isStaff ? `<button class="btn btn-danger btn-sm" onclick="App.deleteClass(${cl.id})"><i class="fa-solid fa-trash"></i> Hapus</button>` : \'—\'}</td>',

    '<div class="section-title">🏛️ Kelas & Rombel</div>' =>
    '<div class="section-title"><i class="fa-solid fa-building-columns"></i> Kelas & Rombel</div>',

    '${isStaff ? `<button class="btn btn-primary" onclick="App.openAddClassModal()">➕ Tambah Kelas</button>` : \'\'}' =>
    '${isStaff ? `<button class="btn btn-primary" onclick="App.openAddClassModal()"><i class="fa-solid fa-plus"></i> Tambah Kelas</button>` : \'\'}',

    '${isStaff ? `<button class="btn btn-primary" onclick="App.openAddScheduleModal()">➕ Tambah Jadwal</button>` : \'\'}' =>
    '${isStaff ? `<button class="btn btn-primary" onclick="App.openAddScheduleModal()"><i class="fa-solid fa-plus"></i> Tambah Jadwal</button>` : \'\'}',

    '<button class="btn btn-secondary" style="flex:1;justify-content:center;" onclick="App.openMonitoringModal(${q.id})">🛡️ Monitoring</button>' =>
    '<button class="btn btn-secondary" style="flex:1;justify-content:center;" onclick="App.openMonitoringModal(${q.id})"><i class="fa-solid fa-shield-halved"></i> Monitoring</button>',

    'title="Edit Ujian" style="flex:1;">✏️ Edit</button>' =>
    'title="Edit Ujian" style="flex:1;"><i class="fa-solid fa-pen"></i> Edit</button>',

    'title="Hapus Ujian" style="flex:1;">🗑️ Hapus</button>' =>
    'title="Hapus Ujian" style="flex:1;"><i class="fa-solid fa-trash"></i> Hapus</button>',

    '<div class="section-title">📝 Manajemen Ujian CBT</div>' =>
    '<div class="section-title"><i class="fa-solid fa-file-pen"></i> Manajemen Ujian CBT</div>',

    '<button class="btn btn-primary" onclick="App.openCreateQuizModal()">➕ Buat Ujian Baru</button>' =>
    '<button class="btn btn-primary" onclick="App.openCreateQuizModal()"><i class="fa-solid fa-plus"></i> Buat Ujian Baru</button>',

    '<span class="badge badge-danger">🔒 3/3 DISQ</span>' =>
    '<span class="badge badge-danger"><i class="fa-solid fa-lock"></i> 3/3 DISQ</span>',

    '<span class="badge badge-amber">⚠️ 2/3</span>' =>
    '<span class="badge badge-amber"><i class="fa-solid fa-triangle-exclamation"></i> 2/3</span>',

    '<span class="badge badge-amber">⚠️ 1/3</span>' =>
    '<span class="badge badge-amber"><i class="fa-solid fa-triangle-exclamation"></i> 1/3</span>',

    '<span class="badge badge-green">✓ CLEAN</span>' =>
    '<span class="badge badge-green"><i class="fa-solid fa-check"></i> CLEAN</span>',

    '<span class="text-green" style="font-size:0.82rem;">✓ Tidak ada pelanggaran</span>' =>
    '<span class="text-green" style="font-size:0.82rem;"><i class="fa-solid fa-check"></i> Tidak ada pelanggaran</span>',

    '<td><button class="btn btn-amber btn-sm" onclick="App.resetStudentExam(${att.id},\'${this.escHtml(att.student_name)}\',${quizId})">🔄 Reset & Buka</button></td>' =>
    '<td><button class="btn btn-amber btn-sm" onclick="App.resetStudentExam(${att.id},\'${this.escHtml(att.student_name)}\',${quizId})"><i class="fa-solid fa-rotate-left"></i> Reset & Buka</button></td>',

    "this.openModal('🛡️ Incident Forensics & Monitoring Ujian'," =>
    "this.openModal('Incident Forensics & Monitoring Ujian',",

    '`⚠️ Reset Ujian — "${studentName}"' =>
    '`Reset Ujian — "${studentName}"',

    '`✅ Ujian "${studentName}" berhasil direset.`' =>
    '`Ujian "${studentName}" berhasil direset.`',

    '`❌ ${d.message}`' =>
    '`${d.message}`',

    "att.status === 'completed'   ? `<span class=\"badge badge-green\">✅ Selesai</span>` :" =>
    "att.status === 'completed'   ? `<span class=\"badge badge-green\"><i class=\"fa-solid fa-circle-check\"></i> Selesai</span>` :",

    "att.status === 'in_progress' ? `<span class=\"badge badge-cyan\">🔄 Berlangsung</span>` :" =>
    "att.status === 'in_progress' ? `<span class=\"badge badge-cyan\"><i class=\"fa-solid fa-rotate\"></i> Berlangsung</span>` :",

    "att.status === 'disqualified'? `<span class=\"badge badge-danger\">🚫 Diskualifikasi</span>` :" =>
    "att.status === 'disqualified'? `<span class=\"badge badge-danger\"><i class=\"fa-solid fa-ban\"></i> Diskualifikasi</span>` :",

    "? `<span class=\"badge badge-danger\">\${att.violations_count}x ⚠️</span>`" =>
    "? `<span class=\"badge badge-danger\">\${att.violations_count}x <i class=\"fa-solid fa-triangle-exclamation\"></i></span>`",

    "🔄 Reset" => "<i class=\"fa-solid fa-rotate-left\"></i> Reset",
    "💡 Klik <b>Reset</b>" => "<i class=\"fa-solid fa-circle-info\"></i> Klik <b>Reset</b>",

    '<div style="font-size:1.5rem;margin-bottom:0.3rem;">🚫</div>' =>
    '<div style="font-size:1.5rem;margin-bottom:0.3rem;color:var(--danger);"><i class="fa-solid fa-ban"></i></div>',

    '<div style="font-size:1.4rem;margin-bottom:0.3rem;">${lulus ? \'✅\' : \'📋\'}</div>' =>
    '<div style="font-size:1.4rem;margin-bottom:0.3rem;">${lulus ? \'<i class="fa-solid fa-circle-check" style="color:var(--emerald);"></i>\' : \'<i class="fa-solid fa-file-lines"></i>\'}</div>',

    '<button class="btn btn-secondary btn-sm btn-full" onclick="App.openExamReview(${att.id})">🔍 Lihat Pembahasan & Jawaban</button>' =>
    '<button class="btn btn-secondary btn-sm btn-full" onclick="App.openExamReview(${att.id})"><i class="fa-solid fa-square-poll-vertical"></i> Lihat Pembahasan & Jawaban</button>',

    '<div class="section-title">📝 Ujian CBT Online</div>' =>
    '<div class="section-title"><i class="fa-solid fa-file-pen"></i> Ujian CBT Online</div>',

    "this.openModal('🔒 Konfirmasi Ujian & Perizinan Kamera'," =>
    "this.openModal('Konfirmasi Ujian & Perizinan Kamera',",

    '<div style="font-size:3rem;margin-bottom:0.5rem;">📷</div>' =>
    '<div style="font-size:3rem;margin-bottom:0.5rem;color:var(--cyan);"><i class="fa-solid fa-camera"></i></div>',

    '<div style="font-size:1.8rem;margin-bottom:0.2rem;">📹</div>' =>
    '<div style="font-size:1.8rem;margin-bottom:0.2rem;color:var(--cyan);"><i class="fa-solid fa-video"></i></div>',

    '<span class="badge badge-amber">⚠️ Browser tidak mendukung kamera. Mode standar aktif.</span>' =>
    '<span class="badge badge-amber"><i class="fa-solid fa-triangle-exclamation"></i> Browser tidak mendukung kamera. Mode standar aktif.</span>',

    '<span class="badge badge-green">✅ Kamera Aktif — Siap Ujian</span>' =>
    '<span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Kamera Aktif — Siap Ujian</span>',

    "let msg = '⚠️ Kamera tidak dapat diakses.';" =>
    "let msg = 'Kamera tidak dapat diakses.';",

    "msg = '🚫 Izin kamera ditolak. Ujian berjalan tanpa proctoring kamera.';" =>
    "msg = 'Izin kamera ditolak. Ujian berjalan tanpa proctoring kamera.';",

    "msg = '📷 Tidak ada kamera terdeteksi. Ujian berjalan tanpa proctoring kamera.';" =>
    "msg = 'Tidak ada kamera terdeteksi. Ujian berjalan tanpa proctoring kamera.';",

    "badgeEl.innerHTML = '🟢 Menginisialisasi Kamera…';" =>
    "badgeEl.innerHTML = '<i class=\"fa-solid fa-circle-notch fa-spin\"></i> Menginisialisasi Kamera…';",

    "badgeEl.innerHTML = '🟢 Wajah Terdeteksi';" =>
    "badgeEl.innerHTML = '<i class=\"fa-solid fa-circle\" style=\"color:#10b981;font-size:0.65rem;\"></i> Wajah Terdeteksi';",

    "badgeEl.innerHTML = `⚠️ Wajah Hilang (\${this._missingFaceCount}/3)`;" =>
    "badgeEl.innerHTML = `<i class=\"fa-solid fa-triangle-exclamation\" style=\"color:#ef4444;\"></i> Wajah Hilang (\${this._missingFaceCount}/3)`;",

    "const icon = isFace ? '📷' : '⚠️';" =>
    "const icon = isFace ? '<i class=\"fa-solid fa-camera\"></i>' : '<i class=\"fa-solid fa-triangle-exclamation\"></i>';",

    '<div style="font-size:4rem;color:var(--danger);margin-bottom:0.5rem;">🚫</div>' =>
    '<div style="font-size:4rem;color:var(--danger);margin-bottom:0.5rem;"><i class="fa-solid fa-ban"></i></div>',

    '<div class="section-title">🛡️ Laman Pengawasan Ujian Real-Time (Proctoring Center)</div>' =>
    '<div class="section-title"><i class="fa-solid fa-shield-halved"></i> Laman Pengawasan Ujian Real-Time (Proctoring Center)</div>',

    '<button class="btn btn-secondary" onclick="App.refreshProctoringGrid()">🔄 Refresh Manual</button>' =>
    '<button class="btn btn-secondary" onclick="App.refreshProctoringGrid()"><i class="fa-solid fa-rotate"></i> Refresh Manual</button>',

    '<div class="empty-icon">🛡️</div>' =>
    '<div class="empty-icon"><i class="fa-solid fa-shield-halved"></i></div>',

    'else if (isComp) statusBadge = `<span class="badge badge-green">✅ Selesai</span>`;' =>
    'else if (isComp) statusBadge = `<span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Selesai</span>`;',

    '⚠️ ${(l.created_at || \'\').substring(11,19)} - ${this.escHtml(l.details)}' =>
    '<i class="fa-solid fa-triangle-exclamation"></i> ${(l.created_at || \'\').substring(11,19)} - ${this.escHtml(l.details)}',

    '📷 ${snapTime}' =>
    '<i class="fa-solid fa-camera"></i> ${snapTime}',

    '<div class="section-title">📊 Rekap Absensi Siswa</div>' =>
    '<div class="section-title"><i class="fa-solid fa-chart-column"></i> Rekap Absensi Siswa</div>',

    '📆 Semester' => '<i class="fa-solid fa-calendar-days"></i> Semester',

    '<button class="btn btn-primary" onclick="App.fetchRekap()">🔍 Tampilkan Rekap</button>' =>
    '<button class="btn btn-primary" onclick="App.fetchRekap()"><i class="fa-solid fa-magnifying-glass"></i> Tampilkan Rekap</button>',

    '<div class="empty-icon">📊</div>' =>
    '<div class="empty-icon"><i class="fa-solid fa-chart-column"></i></div>',

    '<div class="card-title" style="margin-bottom:0.75rem;">📊 Tren Kehadiran per Bulan</div>' =>
    '<div class="card-title" style="margin-bottom:0.75rem;"><i class="fa-solid fa-chart-line"></i> Tren Kehadiran per Bulan</div>',

    '<div class="card-title" style="margin-bottom:0.75rem;">🍩 Komposisi Kehadiran</div>' =>
    '<div class="card-title" style="margin-bottom:0.75rem;"><i class="fa-solid fa-chart-pie"></i> Komposisi Kehadiran</div>',

    '<td>${r.icon || \'📚\'} ${this.escHtml(r.course_name)}</td>' =>
    '<td>${r.icon && r.icon.startsWith(\'fa-\') ? `<i class="fa-solid ${r.icon}"></i>` : `<i class="fa-solid fa-book"></i>`} ${this.escHtml(r.course_name)}</td>',

    '<button class="btn btn-outline" style="padding:0.55rem 1rem;" onclick="App.markAllPresent()">✅ ${this.t(\'mark_all_present\')}</button>' =>
    '<button class="btn btn-outline" style="padding:0.55rem 1rem;" onclick="App.markAllPresent()"><i class="fa-solid fa-circle-check"></i> ${this.t(\'mark_all_present\')}</button>',
];

foreach ($map as $s => $r) {
    $code = str_replace($s, $r, $code);
}

file_put_contents($file, $code);
echo "Cleaned all emojis in app.js successfully.\n";

