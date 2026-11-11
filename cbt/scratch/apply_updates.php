<?php
// cbt/scratch/apply_updates.php
$file = 'c:/xampp/htdocs/moodle/cbt/js/app.js';
$code = file_get_contents('c:/xampp/htdocs/moodle/cbt/js/app.js.bak'); // start fresh from clean backup

// Normalize newlines to \n
$code = str_replace("\r\n", "\n", $code);

// 1. Initialize savedEssays in startExam
$code = str_replace(
"this.savedAnswers         = data.saved_answers || {};
        this.flaggedQuestions     = {};",
"this.savedAnswers         = data.saved_answers || {};
        this.savedEssays          = data.saved_essays || {};
        this.flaggedQuestions     = {};",
$code
);

// 2. Add handleEssayInput, clearChoice, generateMCExplanation, generateEssayExplanation
$targetMethods = <<<'TARGET'
  selectOption(qId, key) {
    this.savedAnswers[qId] = key;
    this.renderExamScreen();
    fetch('api/exam.php?action=save_answer', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ attempt_id: this.attemptId, question_id: qId, selected_option: key }) });
  },

  clearChoice(qId) {
    delete this.savedAnswers[qId];
    this.renderExamScreen();
    fetch('api/exam.php?action=save_answer', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ attempt_id: this.attemptId, question_id: qId, selected_option: '' }) });
  },
TARGET;

$replaceMethods = <<<'REPLACE'
  selectOption(qId, key) {
    this.savedAnswers[qId] = key;
    this.renderExamScreen();
    fetch('api/exam.php?action=save_answer', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ attempt_id: this.attemptId, question_id: qId, selected_option: key }) });
  },

  handleEssayInput(qId, val) {
    if (!this.savedEssays) this.savedEssays = {};
    this.savedEssays[qId] = val;

    const countEl = document.getElementById('essay-char-count');
    if (countEl) countEl.innerHTML = `<i class="fa-solid fa-font"></i> ${val.length} karakter`;

    const statusEl = document.getElementById('essay-save-status');
    if (statusEl) statusEl.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan...`;

    clearTimeout(this._essaySaveTimer);
    this._essaySaveTimer = setTimeout(() => {
      fetch('api/exam.php?action=save_answer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          attempt_id: this.attemptId,
          question_id: qId,
          essay_response: val
        })
      }).then(r => r.json()).then(res => {
        if (statusEl) statusEl.innerHTML = `<i class="fa-solid fa-circle-check" style="color:var(--emerald);"></i> Tersimpan`;
        const palNums = document.querySelectorAll('.palette-num');
        if (palNums && palNums[this.currentQuestionIndex]) {
          if (val.trim().length > 0) palNums[this.currentQuestionIndex].classList.add('answered');
          else palNums[this.currentQuestionIndex].classList.remove('answered');
        }
      }).catch(() => {
        if (statusEl) statusEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="color:var(--danger);"></i> Gagal menyimpan`;
      });
    }, 600);
  },

  clearChoice(qId) {
    const q = this.questions ? this.questions.find(item => item.id == qId) : null;
    if (q && q.type === 'essay') {
      if (this.savedEssays) this.savedEssays[qId] = '';
      const box = document.getElementById('essay-input-box');
      if (box) box.value = '';
      const countEl = document.getElementById('essay-char-count');
      if (countEl) countEl.innerHTML = `<i class="fa-solid fa-font"></i> 0 karakter`;
      const statusEl = document.getElementById('essay-save-status');
      if (statusEl) statusEl.innerHTML = '';
      const palNums = document.querySelectorAll('.palette-num');
      if (palNums && palNums[this.currentQuestionIndex]) {
        palNums[this.currentQuestionIndex].classList.remove('answered');
      }
      fetch('api/exam.php?action=save_answer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ attempt_id: this.attemptId, question_id: qId, essay_response: '' })
      });
    } else {
      delete this.savedAnswers[qId];
      this.renderExamScreen();
      fetch('api/exam.php?action=save_answer', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ attempt_id: this.attemptId, question_id: qId, selected_option: '' }) });
    }
  },

  generateMCExplanation(q, isCor, userAns, corAns, corText) {
    return `Soal ini menguji pemahaman konsep secara terarah. Jawaban yang tepat dan valid adalah <b>Opsi ${corAns}</b> (<i>${this.escHtml(corText)}</i>) karena telah sesuai dengan standar materi dan kompetensi yang dipelajari. Opsi lainnya merupakan pengecoh yang memiliki kekeliruan konsep atau tidak sesuai dengan pertanyaan yang diajukan.`;
  },

  generateEssayExplanation(q) {
    return `Soal uraian / essay ini bertujuan mengevaluasi daya nalar, kedalaman konsep, dan kemampuan siswa dalam menguraikan materi secara sistematis. Evaluasi penilaian dinilai dari kesesuaian uraian terhadap kriteria rubrik acuan pengampu serta kelengkapan fakta dan argumen logis yang disajikan.`;
  },
REPLACE;

if (strpos($code, $targetMethods) !== false) {
    $code = str_replace($targetMethods, $replaceMethods, $code);
    echo "1. Replaced methods successfully.\n";
} else {
    echo "Error: targetMethods not found!\n";
}

// 3. Replace renderExamScreen
$targetExamScreen = <<<'TARGET'
  // EXAM SCREEN RENDER (With Large Camera Under Question Navigation)
  renderExamScreen() {
    const q           = this.questions[this.currentQuestionIndex];
    const saved       = this.savedAnswers[q.id] || '';
    const flagged     = !!this.flaggedQuestions[q.id];
    const answered    = Object.keys(this.savedAnswers).length;
    const total       = this.questions.length;
    const violations  = window.ExamGuard ? window.ExamGuard.violationCount : 0;

    document.getElementById('main-content').innerHTML = `
      <div class="exam-shell">

        <!-- Exam Top Bar -->
        <div class="exam-topbar">
          <div class="exam-title-block">
            <div style="display:flex;gap:0.5rem;margin-bottom:0.25rem;">
              <span class="badge badge-cyan" style="font-size:0.68rem;">SESI AKTIF</span>
              <span class="badge badge-danger" style="font-size:0.68rem;">LOCKDOWN CBT</span>
            </div>
            <h3>${this.escHtml(this.currentQuiz.title)}</h3>
            <small>Kandidat: <b>${this.escHtml(this.currentUser.name)}</b> (${this.currentUser.nip_nis || this.currentUser.username}) — SMKN 1 CIBINONG</small>
          </div>

          <div class="exam-topbar-right">
            <!-- Sleek Proctor Status Pill -->
            <div style="display:flex;align-items:center;gap:0.45rem;background:var(--bg-card);padding:0.4rem 0.8rem;border-radius:var(--r-md);border:1px solid var(--border);">
              <span style="display:inline-block;width:8px;height:8px;background:var(--emerald);border-radius:50%;box-shadow:0 0 6px var(--emerald);"></span>
              <span style="font-size:0.75rem;font-weight:700;color:var(--text-sub);">PROCTORING ON</span>
            </div>

            <!-- Timer -->
            <div class="exam-timer">
              <div class="exam-timer-label">Sisa Waktu</div>
              <div class="exam-timer-value" id="exam-timer-display">--:--</div>
            </div>
          </div>
        </div>

        <!-- Violation Strip -->
        <div class="violation-strip">
          <span>⚠️</span>
          <span><b>ANTI-CHEAT AKTIF</b> — Berpindah tab akan dicatat. Pelanggaran: <b id="viol-count-display" style="color:#fca5a5;font-size:1rem;">${violations} / 3</b> (maks. 3x → diskualifikasi)</span>
        </div>

        <!-- Exam Body -->
        <div class="exam-body">

          <!-- Question Card -->
          <div class="question-card">
            <div class="question-meta">
              <div style="display:flex;align-items:center;gap:0.75rem;">
                <span class="question-num">SOAL ${this.currentQuestionIndex + 1} / ${total}</span>
                <span class="badge badge-muted">Pilihan Ganda</span>
                <span class="badge badge-muted">10 Poin</span>
              </div>
              <button class="btn btn-sm ${flagged ? 'btn-danger' : 'btn-secondary'}" onclick="App.toggleFlag(${q.id})">
                ${flagged ? '★ Ditandai Ragu' : '☆ Tandai Ragu-ragu'}
              </button>
            </div>

            <div class="question-text">${this.escHtml(q.question_text)}</div>

            <div class="options-list">
              ${['A','B','C','D'].map(k => {
                const text  = q['option_' + k.toLowerCase()];
                const sel   = saved === k;
                return `
                  <div class="option-item ${sel ? 'selected' : ''}" onclick="App.selectOption(${q.id},'${k}')">
                    <div class="option-key">${k}</div>
                    <div class="option-text">${this.escHtml(text)}</div>
                    ${sel ? `<span class="badge badge-cyan" style="margin-left:auto;flex-shrink:0;">✓ Dipilih</span>` : ''}
                  </div>
                `;
              }).join('')}
            </div>

            <div class="question-nav-bar">
              <button class="btn btn-secondary" onclick="App.prevQ()" ${this.currentQuestionIndex === 0 ? 'disabled' : ''}>⬅️ Sebelumnya</button>
              <div style="display:flex;gap:0.6rem;align-items:center;">
                ${saved ? `<button class="btn btn-secondary btn-sm" onclick="App.clearChoice(${q.id})">🗑 Bersihkan</button>` : ''}
                ${this.currentQuestionIndex === total - 1
                  ? `<button class="btn btn-primary" onclick="App.confirmSubmit()">✅ Selesai & Kirim</button>`
                  : `<button class="btn btn-primary" onclick="App.nextQ()">Selanjutnya ➡️</button>`}
              </div>
            </div>
          </div>

          <!-- Navigation Palette with Large Camera Underneath -->
          <div class="nav-palette-card">
            <div class="palette-header">
              <div class="palette-title">🗂 Navigasi Soal</div>
              <span class="badge badge-cyan">${answered}/${total}</span>
            </div>
            <div class="palette-legend">
              <div style="display:flex;align-items:center;gap:0.3rem;"><div class="legend-dot" style="background:var(--cyan);"></div>Aktif</div>
              <div style="display:flex;align-items:center;gap:0.3rem;"><div class="legend-dot" style="background:var(--emerald);"></div>Dijawab</div>
              <div style="display:flex;align-items:center;gap:0.3rem;"><div class="legend-dot" style="background:var(--danger);"></div>Ragu</div>
              <div style="display:flex;align-items:center;gap:0.3rem;"><div class="legend-dot" style="background:var(--bg-input);border:1px solid var(--border-subtle);"></div>Belum</div>
            </div>
            <div class="palette-grid">
              ${this.questions.map((item, idx) => {
                const cls = [
                  idx === this.currentQuestionIndex ? 'active' : '',
                  this.savedAnswers[item.id]    ? 'answered' : '',
                  this.flaggedQuestions[item.id] ? 'flagged'  : '',
                ].filter(Boolean).join(' ');
                return `<div class="palette-num ${cls}" onclick="App.goToQ(${idx})">${idx + 1}${this.flaggedQuestions[item.id] ? '<span class="palette-flag-star">★</span>' : ''}</div>`;
              }).join('')}
            </div>

            <!-- LARGE PROCTOR CAMERA (Directly Below Question Numbers) -->
            <div class="exam-camera-box-large">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;">
                <span style="font-size:0.75rem;font-weight:700;color:var(--text-main);display:flex;align-items:center;gap:0.35rem;">
                  📷 KAMERA PENGAWAS
                </span>
                <span style="font-size:0.7rem;font-weight:700;color:var(--danger);display:flex;align-items:center;gap:0.3rem;">
                  <span style="display:inline-block;width:7px;height:7px;background:var(--danger);border-radius:50%;box-shadow:0 0 6px var(--danger);"></span> REC
                </span>
              </div>

              <div class="cam-screen-large" id="cam-screen-wrapper">
                <video id="proctor-live-video" autoplay playsinline muted></video>
                <div class="cam-scan-overlay"></div>
                <div class="cam-rec-dot" style="position:absolute;top:8px;right:8px;z-index:4;"></div>
                <div id="proctor-face-badge" class="face-status-pill detected">
                  🟢 Wajah Terdeteksi
                </div>
              </div>
              <small style="display:block;color:var(--text-muted);font-size:0.7rem;margin-top:0.35rem;text-align:center;">
                Wajah wajib terlihat jelas. Wajah hilang = Peringatan!
              </small>
            </div>

            <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border-subtle);">
              <button class="btn btn-primary btn-full" onclick="App.confirmSubmit()">✅ Selesaikan Ujian</button>
            </div>
          </div>

        </div>
      </div>
    `;
TARGET;

$replaceExamScreen = <<<'REPLACE'
  // EXAM SCREEN RENDER (Supports Multiple Choice & Essay with Real-time Input)
  renderExamScreen() {
    const q           = this.questions[this.currentQuestionIndex];
    const isEssay     = (q.type === 'essay');
    const savedOpt    = this.savedAnswers[q.id] || '';
    const savedEssay  = (this.savedEssays && this.savedEssays[q.id]) ? this.savedEssays[q.id] : '';
    const flagged     = !!this.flaggedQuestions[q.id];
    const answered    = this.questions.filter(item => {
      if (item.type === 'essay') {
        return !!(this.savedEssays && this.savedEssays[item.id] && this.savedEssays[item.id].trim().length > 0);
      }
      return !!this.savedAnswers[item.id];
    }).length;
    const total       = this.questions.length;
    const violations  = window.ExamGuard ? window.ExamGuard.violationCount : 0;
    const hasCurrentAnswer = isEssay ? !!savedEssay.trim() : !!savedOpt;

    document.getElementById('main-content').innerHTML = `
      <div class="exam-shell">

        <!-- Exam Top Bar -->
        <div class="exam-topbar">
          <div class="exam-title-block">
            <div style="display:flex;gap:0.5rem;margin-bottom:0.25rem;">
              <span class="badge badge-cyan" style="font-size:0.68rem;"><i class="fa-solid fa-circle-dot"></i> SESI AKTIF</span>
              <span class="badge badge-danger" style="font-size:0.68rem;"><i class="fa-solid fa-shield-halved"></i> LOCKDOWN CBT</span>
            </div>
            <h3>${this.escHtml(this.currentQuiz.title)}</h3>
            <small>Kandidat: <b>${this.escHtml(this.currentUser.name)}</b> (${this.currentUser.nip_nis || this.currentUser.username}) — SMKN 1 CIBINONG</small>
          </div>

          <div class="exam-topbar-right">
            <!-- Sleek Proctor Status Pill -->
            <div style="display:flex;align-items:center;gap:0.45rem;background:var(--bg-card);padding:0.4rem 0.8rem;border-radius:var(--r-md);border:1px solid var(--border);">
              <span style="display:inline-block;width:8px;height:8px;background:var(--emerald);border-radius:50%;box-shadow:0 0 6px var(--emerald);"></span>
              <span style="font-size:0.75rem;font-weight:700;color:var(--text-sub);">PROCTORING ON</span>
            </div>

            <!-- Timer -->
            <div class="exam-timer">
              <div class="exam-timer-label">Sisa Waktu</div>
              <div class="exam-timer-value" id="exam-timer-display">--:--</div>
            </div>
          </div>
        </div>

        <!-- Violation Strip -->
        <div class="violation-strip">
          <i class="fa-solid fa-triangle-exclamation" style="font-size:1.1rem;color:#fca5a5;"></i>
          <span><b>ANTI-CHEAT AKTIF</b> — Berpindah tab akan dicatat. Pelanggaran: <b id="viol-count-display" style="color:#fca5a5;font-size:1rem;">${violations} / 3</b> (maks. 3x → diskualifikasi)</span>
        </div>

        <!-- Exam Body -->
        <div class="exam-body">

          <!-- Question Card -->
          <div class="question-card">
            <div class="question-meta">
              <div style="display:flex;align-items:center;gap:0.75rem;">
                <span class="question-num">SOAL ${this.currentQuestionIndex + 1} / ${total}</span>
                <span class="badge ${isEssay ? 'badge-amber' : 'badge-cyan'}">
                  <i class="fa-solid ${isEssay ? 'fa-pen-nib' : 'fa-list-check'}"></i> ${isEssay ? 'Essai / Uraian' : 'Pilihan Ganda'}
                </span>
                <span class="badge badge-muted"><i class="fa-solid fa-star"></i> ${q.points || 10} Poin</span>
              </div>
              <button class="btn btn-sm ${flagged ? 'btn-danger' : 'btn-secondary'}" onclick="App.toggleFlag(${q.id})">
                ${flagged ? '<i class="fa-solid fa-star"></i> Ditandai Ragu' : '<i class="fa-regular fa-star"></i> Tandai Ragu-ragu'}
              </button>
            </div>

            <div class="question-text">${this.escHtml(q.question_text)}</div>

            ${isEssay ? `
              <!-- Essay Question Input Field -->
              <div class="essay-input-container">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.6rem;">
                  <label class="form-label" style="font-weight:700;margin:0;display:flex;align-items:center;gap:0.45rem;font-size:0.95rem;color:var(--text-main);">
                    <i class="fa-solid fa-pen-to-square" style="color:var(--cyan);"></i> Ketikkan Jawaban Uraian Anda di Bawah Ini:
                  </label>
                  <span id="essay-save-status" style="font-size:0.75rem;color:var(--emerald);font-weight:600;">
                    ${savedEssay.trim() ? '<i class="fa-solid fa-circle-check"></i> Tersimpan' : ''}
                  </span>
                </div>
                <textarea id="essay-input-box" class="form-control" rows="8" placeholder="Tuliskan penjelasan atau jawaban essay Anda secara lengkap, runtut, dan jelas di sini..." oninput="App.handleEssayInput(${q.id}, this.value)">${this.escHtml(savedEssay)}</textarea>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.6rem;font-size:0.78rem;color:var(--text-muted);">
                  <span id="essay-char-count"><i class="fa-solid fa-font"></i> ${savedEssay.length} karakter</span>
                  <span><i class="fa-solid fa-cloud-arrow-up"></i> Jawaban tersimpan otomatis saat Anda mengetik</span>
                </div>
              </div>
            ` : `
              <!-- Multiple Choice Options -->
              <div class="options-list">
                ${['A','B','C','D'].map(k => {
                  const text  = q['option_' + k.toLowerCase()];
                  if (!text) return '';
                  const sel   = savedOpt === k;
                  return `
                    <div class="option-item ${sel ? 'selected' : ''}" onclick="App.selectOption(${q.id},'${k}')">
                      <div class="option-key">${k}</div>
                      <div class="option-text">${this.escHtml(text)}</div>
                      ${sel ? `<span class="badge badge-cyan" style="margin-left:auto;flex-shrink:0;"><i class="fa-solid fa-check"></i> Dipilih</span>` : ''}
                    </div>
                  `;
                }).join('')}
              </div>
            `}

            <div class="question-nav-bar">
              <button class="btn btn-secondary" onclick="App.prevQ()" ${this.currentQuestionIndex === 0 ? 'disabled' : ''}>
                <i class="fa-solid fa-arrow-left"></i> Sebelumnya
              </button>
              <div style="display:flex;gap:0.6rem;align-items:center;">
                ${hasCurrentAnswer ? `<button class="btn btn-secondary btn-sm" onclick="App.clearChoice(${q.id})"><i class="fa-solid fa-trash"></i> Bersihkan</button>` : ''}
                ${this.currentQuestionIndex === total - 1
                  ? `<button class="btn btn-primary" onclick="App.confirmSubmit()"><i class="fa-solid fa-paper-plane"></i> Selesai & Kirim</button>`
                  : `<button class="btn btn-primary" onclick="App.nextQ()">Selanjutnya <i class="fa-solid fa-arrow-right"></i></button>`}
              </div>
            </div>
          </div>

          <!-- Navigation Palette with Large Camera Underneath -->
          <div class="nav-palette-card">
            <div class="palette-header">
              <div class="palette-title"><i class="fa-solid fa-layer-group"></i> Navigasi Soal</div>
              <span class="badge badge-cyan">${answered}/${total}</span>
            </div>
            <div class="palette-legend">
              <div style="display:flex;align-items:center;gap:0.3rem;"><div class="legend-dot" style="background:var(--cyan);"></div>Aktif</div>
              <div style="display:flex;align-items:center;gap:0.3rem;"><div class="legend-dot" style="background:var(--emerald);"></div>Dijawab</div>
              <div style="display:flex;align-items:center;gap:0.3rem;"><div class="legend-dot" style="background:var(--danger);"></div>Ragu</div>
              <div style="display:flex;align-items:center;gap:0.3rem;"><div class="legend-dot" style="background:var(--bg-input);border:1px solid var(--border-subtle);"></div>Belum</div>
            </div>
            <div class="palette-grid">
              ${this.questions.map((item, idx) => {
                const hasAns = (item.type === 'essay')
                  ? !!(this.savedEssays && this.savedEssays[item.id] && this.savedEssays[item.id].trim().length > 0)
                  : !!this.savedAnswers[item.id];
                const cls = [
                  idx === this.currentQuestionIndex ? 'active' : '',
                  hasAns ? 'answered' : '',
                  this.flaggedQuestions[item.id] ? 'flagged'  : '',
                ].filter(Boolean).join(' ');
                return `<div class="palette-num ${cls}" onclick="App.goToQ(${idx})">${idx + 1}${this.flaggedQuestions[item.id] ? '<span class="palette-flag-star"><i class="fa-solid fa-star" style="font-size:0.55rem;"></i></span>' : ''}</div>`;
              }).join('')}
            </div>

            <!-- LARGE PROCTOR CAMERA (Directly Below Question Numbers) -->
            <div class="exam-camera-box-large">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;">
                <span style="font-size:0.75rem;font-weight:700;color:var(--text-main);display:flex;align-items:center;gap:0.35rem;">
                  <i class="fa-solid fa-video"></i> KAMERA PENGAWAS
                </span>
                <span style="font-size:0.7rem;font-weight:700;color:var(--danger);display:flex;align-items:center;gap:0.3rem;">
                  <span style="display:inline-block;width:7px;height:7px;background:var(--danger);border-radius:50%;box-shadow:0 0 6px var(--danger);"></span> REC
                </span>
              </div>

              <div class="cam-screen-large" id="cam-screen-wrapper">
                <video id="proctor-live-video" autoplay playsinline muted></video>
                <div class="cam-scan-overlay"></div>
                <div class="cam-rec-dot" style="position:absolute;top:8px;right:8px;z-index:4;"></div>
                <div id="proctor-face-badge" class="face-status-pill detected">
                  <i class="fa-solid fa-circle" style="color:#10b981;font-size:0.65rem;"></i> Wajah Terdeteksi
                </div>
              </div>
              <small style="display:block;color:var(--text-muted);font-size:0.7rem;margin-top:0.35rem;text-align:center;">
                Wajah wajib terlihat jelas. Wajah hilang = Peringatan!
              </small>
            </div>

            <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border-subtle);">
              <button class="btn btn-primary btn-full" onclick="App.confirmSubmit()"><i class="fa-solid fa-paper-plane"></i> Selesaikan Ujian</button>
            </div>
          </div>

        </div>
      </div>
    `;
REPLACE;

if (strpos($code, $targetExamScreen) !== false) {
    $code = str_replace($targetExamScreen, $replaceExamScreen, $code);
    echo "2. Replaced renderExamScreen successfully.\n";
} else {
    echo "Error: targetExamScreen not found!\n";
}

// 4. Replace confirmSubmit
$targetConfirmSubmit = <<<'TARGET'
  confirmSubmit() {
    const ans = Object.keys(this.savedAnswers).length;
    const tot = this.questions.length;
    this.openModal('✅ Kirim Jawaban Ujian?', `
      <div style="text-align:center;">
        <div style="font-size:3rem;margin-bottom:0.75rem;">📋</div>
        <p style="color:var(--text-sub);margin-bottom:1rem;font-size:0.92rem;">
          Anda telah menjawab <b style="color:var(--cyan);">${ans} dari ${tot}</b> soal.<br>
          Setelah dikirim, jawaban Anda akan otomatis dievaluasi dan nilai serta pembahasannya akan ditampilkan.
        </p>
        <div style="display:flex;gap:0.5rem;justify-content:center;">
          <button class="btn btn-secondary" onclick="App.closeModal()">Periksa Kembali</button>
          <button class="btn btn-primary" onclick="App.closeModal();App.submitExam(false);">Ya, Kirim Sekarang</button>
        </div>
      </div>
    `);
  },
TARGET;

$replaceConfirmSubmit = <<<'REPLACE'
  confirmSubmit() {
    const ans = this.questions.filter(item => {
      if (item.type === 'essay') {
        return !!(this.savedEssays && this.savedEssays[item.id] && this.savedEssays[item.id].trim().length > 0);
      }
      return !!this.savedAnswers[item.id];
    }).length;
    const tot = this.questions.length;
    this.openModal('Kirim Jawaban Ujian?', `
      <div style="text-align:center;">
        <div style="font-size:2.8rem;margin-bottom:0.75rem;color:var(--cyan);"><i class="fa-solid fa-file-circle-check"></i></div>
        <p style="color:var(--text-sub);margin-bottom:1rem;font-size:0.92rem;">
          Anda telah menjawab <b style="color:var(--cyan);">${ans} dari ${tot}</b> soal.<br>
          Setelah dikirim, ujian selesai dan evaluasi beserta pembahasan menyeluruh akan ditampilkan.
        </p>
        <div style="display:flex;gap:0.5rem;justify-content:center;">
          <button class="btn btn-secondary" onclick="App.closeModal()"><i class="fa-solid fa-arrow-left"></i> Periksa Kembali</button>
          <button class="btn btn-primary" onclick="App.closeModal();App.submitExam(false);"><i class="fa-solid fa-check"></i> Ya, Kirim Sekarang</button>
        </div>
      </div>
    `);
  },
REPLACE;

if (strpos($code, $targetConfirmSubmit) !== false) {
    $code = str_replace($targetConfirmSubmit, $replaceConfirmSubmit, $code);
    echo "3. Replaced confirmSubmit successfully.\n";
} else {
    echo "Error: targetConfirmSubmit not found!\n";
}

// 5. Replace renderExamReview (Comprehensive Evaluation & Explanation for Essay and Multiple Choice)
$targetReview = <<<'TARGET'
  // EXAM RESULT, QUESTION REVIEW & EVALUATION SCREEN
  renderExamReview(d) {
    const questions = d.review || [];
    const score = parseFloat(d.score || 0);
    const totalQ = d.total_questions || questions.length;
    const correctQ = d.correct_answers || questions.filter(q => q.is_correct == 1).length;
    const wrongQ = Math.max(0, totalQ - correctQ);
    const violations = d.violations_count || 0;
    const isPass = score >= 75;

    let predicate = 'Perlu Remedial (D)';
    let predicateColor = 'var(--danger)';
    if (score >= 90) { predicate = 'Istimewa / Sangat Memuaskan (A)'; predicateColor = 'var(--emerald)'; }
    else if (score >= 75) { predicate = 'Baik / Lulus KKM (B)'; predicateColor = 'var(--cyan)'; }
    else if (score >= 60) { predicate = 'Cukup (C)'; predicateColor = 'var(--amber)'; }

    const quizTitle = d.quiz?.title || this.currentQuiz?.title || 'Ujian CBT';
    const courseName = d.quiz?.course_name || this.currentQuiz?.course_name || 'Mata Pelajaran';

    this.setContent(`
      <div style="max-width:920px;margin:0 auto;padding-bottom:3rem;" id="exam-review-container">
        
        <!-- Action bar top -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:0.75rem;">
          <div>
            <div style="font-size:0.8rem;font-weight:700;color:var(--cyan);text-transform:uppercase;letter-spacing:0.5px;">HASIL & EVALUASI CBT</div>
            <h2 style="margin:0;font-size:1.6rem;color:var(--text-main);">${this.escHtml(quizTitle)}</h2>
            <div style="font-size:0.85rem;color:var(--text-muted);">${this.escHtml(courseName)} · Siswa: <b>${this.escHtml(this.currentUser.name)}</b> (${this.currentUser.nip_nis || this.currentUser.username})</div>
          </div>
          <div style="display:flex;gap:0.6rem;">
            <button class="btn btn-secondary" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
            <button class="btn btn-primary" onclick="App.renderAppLayout('dashboard')">🏠 Kembali ke Dashboard</button>
          </div>
        </div>

        <!-- Hero Scorecard -->
        <div class="review-score-hero">
          <div style="display:inline-block;padding:0.4rem 1.25rem;border-radius:999px;background:rgba(0,242,254,0.15);color:var(--cyan);font-weight:800;font-size:0.85rem;margin-bottom:1rem;border:1px solid rgba(0,242,254,0.3);">
            ${isPass ? '🎉 SELAMAT! ANDA LULUS' : '⚠️ PERLU PENINGKATAN / REMEDIAL'}
          </div>
          <div style="font-size:0.85rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;font-weight:700;">NILAI AKHIR ANDA</div>
          <div style="font-family:var(--font-head);font-size:4.5rem;font-weight:900;line-height:1;color:var(--cyan);margin:0.5rem 0;">
            ${score}
          </div>
          <div style="font-size:1.1rem;font-weight:700;color:${predicateColor};margin-bottom:1.5rem;">
            Predikat: ${predicate}
          </div>

          <!-- Quick Stats Grid -->
          <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:0.75rem;max-width:650px;margin:0 auto;">
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--r-md);padding:0.75rem;">
              <div style="font-size:0.72rem;color:var(--text-muted);font-weight:700;">TOTAL SOAL</div>
              <div style="font-size:1.3rem;font-weight:800;color:var(--text-main);">${totalQ}</div>
            </div>
            <div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.3);border-radius:var(--r-md);padding:0.75rem;">
              <div style="font-size:0.72rem;color:#10b981;font-weight:700;">✅ BENAR</div>
              <div style="font-size:1.3rem;font-weight:800;color:#10b981;">${correctQ} Soal</div>
            </div>
            <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);border-radius:var(--r-md);padding:0.75rem;">
              <div style="font-size:0.72rem;color:#ef4444;font-weight:700;">❌ SALAH</div>
              <div style="font-size:1.3rem;font-weight:800;color:#ef4444;">${wrongQ} Soal</div>
            </div>
            <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.3);border-radius:var(--r-md);padding:0.75rem;">
              <div style="font-size:0.72rem;color:#f59e0b;font-weight:700;">⚠️ PELANGGARAN</div>
              <div style="font-size:1.3rem;font-weight:800;color:#f59e0b;">${violations}x</div>
            </div>
          </div>
        </div>

        <!-- Filter bar -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.5rem;">
          <div style="font-weight:800;font-size:1.15rem;color:var(--text-main);">📋 Pembahasan & Kunci Jawaban</div>
          <div style="display:flex;gap:0.4rem;">
            <button class="btn btn-sm btn-outline active" id="btn-filter-all" onclick="App.filterReviewQuestions('all')">Semua (${totalQ})</button>
            <button class="btn btn-sm btn-outline" id="btn-filter-correct" onclick="App.filterReviewQuestions('correct')">✅ Benar (${correctQ})</button>
            <button class="btn btn-sm btn-outline" id="btn-filter-wrong" onclick="App.filterReviewQuestions('wrong')">❌ Salah (${wrongQ})</button>
          </div>
        </div>

        <!-- Question items list -->
        <div id="review-questions-list">
          ${questions.map((q, idx) => {
            const isCor = (q.is_correct == 1);
            const userAns = q.selected_option || '—';
            const corAns = q.correct_option || '—';
            const corText = q['option_' + corAns.toLowerCase()] || '';

            return `
              <div class="review-q-card ${isCor ? 'is-correct' : 'is-wrong'}" data-status="${isCor ? 'correct' : 'wrong'}">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                  <div style="display:flex;align-items:center;gap:0.5rem;">
                    <span style="font-size:0.85rem;font-weight:800;color:var(--text-main);background:var(--bg-canvas);border:1px solid var(--border);padding:0.25rem 0.6rem;border-radius:var(--r-sm);">
                      SOAL ${idx + 1}
                    </span>
                    <span class="badge ${isCor ? 'badge-emerald' : 'badge-danger'}">
                      ${isCor ? '✅ Benar (+10 Poin)' : (userAns === '—' ? '⚪ Tidak Dijawab (0 Poin)' : '❌ Jawaban Salah (0 Poin)')}
                    </span>
                  </div>
                  <div style="font-size:0.8rem;color:var(--text-muted);font-weight:600;">Poin: ${isCor ? q.points || 10 : 0} / ${q.points || 10}</div>
                </div>

                <div style="font-size:1rem;font-weight:600;color:var(--text-main);margin-bottom:1rem;line-height:1.5;">
                  ${this.escHtml(q.question_text)}
                </div>

                <!-- Options List -->
                <div style="margin-bottom:0.75rem;">
                  ${['A','B','C','D'].map(k => {
                    const optText = q['option_' + k.toLowerCase()];
                    if (!optText) return '';
                    const isKeyCorrect = (k === corAns);
                    const isUserPick = (k === userAns);

                    let itemClass = 'review-opt-item';
                    let badgeHtml = '';

                    if (isKeyCorrect) {
                      itemClass += ' opt-correct';
                      badgeHtml = `<span class="badge badge-emerald" style="margin-left:auto;flex-shrink:0;">✅ Kunci Jawaban Benar</span>`;
                    }
                    if (isUserPick && !isKeyCorrect) {
                      itemClass += ' opt-student-wrong';
                      badgeHtml = `<span class="badge badge-danger" style="margin-left:auto;flex-shrink:0;">❌ Jawaban Anda</span>`;
                    } else if (isUserPick && isKeyCorrect) {
                      badgeHtml = `<span class="badge badge-emerald" style="margin-left:auto;flex-shrink:0;">✓ Jawaban Anda (Benar)</span>`;
                    }

                    return `
                      <div class="${itemClass}">
                        <div class="review-opt-key">${k}</div>
                        <div style="flex:1;">${this.escHtml(optText)}</div>
                        ${badgeHtml}
                      </div>
                    `;
                  }).join('')}
                </div>

                <!-- Explanation & Evaluation Box -->
                <div class="review-eval-box ${isCor ? 'eval-correct' : 'eval-wrong'}">
                  <div style="font-size:1.2rem;line-height:1;">${isCor ? '💡' : '📌'}</div>
                  <div style="flex:1;">
                    <div style="font-weight:700;margin-bottom:0.25rem;">
                      ${isCor ? 'Evaluasi: Jawaban Anda Tepat!' : 'Evaluasi Jawaban:'}
                    </div>
                    <div style="font-size:0.85rem;color:var(--text-sub);">
                      ${isCor 
                        ? `Pilihan Anda <b>(${userAns})</b> sudah sesuai dengan kunci jawaban yang benar.` 
                        : (userAns === '—' 
                            ? `Anda belum memilih jawaban pada nomor ini. Kunci jawaban yang benar adalah <b>Opsi ${corAns}: "${this.escHtml(corText)}"</b>.` 
                            : `Anda memilih <b>Opsi ${userAns}</b>. Jawaban yang tepat seharusnya adalah <b>Opsi ${corAns}: "${this.escHtml(corText)}"</b>.`)}
                    </div>
                  </div>
                </div>

              </div>
            `;
          }).join('')}
        </div>

        <div style="text-align:center;margin-top:2rem;">
          <button class="btn btn-primary btn-lg" onclick="App.renderAppLayout('dashboard')">
            🏠 Selesai & Kembali ke Dashboard
          </button>
        </div>

      </div>
    `);
  },
TARGET;

$replaceReview = <<<'REPLACE'
  // EXAM RESULT, QUESTION REVIEW & COMPREHENSIVE EVALUATION SCREEN
  renderExamReview(d) {
    const questions = d.review || [];
    const score = parseFloat(d.score || 0);
    const totalQ = d.total_questions || questions.length;
    const correctQ = d.correct_answers || questions.filter(q => q.is_correct == 1).length;
    const wrongQ = Math.max(0, totalQ - correctQ);
    const violations = d.violations_count || 0;
    const isPass = score >= 75;

    let predicate = 'Perlu Remedial (D)';
    let predicateColor = 'var(--danger)';
    if (score >= 90) { predicate = 'Istimewa / Sangat Memuaskan (A)'; predicateColor = 'var(--emerald)'; }
    else if (score >= 75) { predicate = 'Baik / Lulus KKM (B)'; predicateColor = 'var(--cyan)'; }
    else if (score >= 60) { predicate = 'Cukup (C)'; predicateColor = 'var(--amber)'; }

    const quizTitle = d.quiz?.title || this.currentQuiz?.title || 'Ujian CBT';
    const courseName = d.quiz?.course_name || this.currentQuiz?.course_name || 'Mata Pelajaran';

    this.setContent(`
      <div style="max-width:920px;margin:0 auto;padding-bottom:3rem;" id="exam-review-container">
        
        <!-- Action bar top -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:0.75rem;">
          <div>
            <div style="font-size:0.8rem;font-weight:700;color:var(--cyan);text-transform:uppercase;letter-spacing:0.5px;display:flex;align-items:center;gap:0.4rem;">
              <i class="fa-solid fa-square-poll-vertical"></i> HASIL & EVALUASI CBT
            </div>
            <h2 style="margin:0;font-size:1.6rem;color:var(--text-main);">${this.escHtml(quizTitle)}</h2>
            <div style="font-size:0.85rem;color:var(--text-muted);">${this.escHtml(courseName)} · Siswa: <b>${this.escHtml(this.currentUser.name)}</b> (${this.currentUser.nip_nis || this.currentUser.username})</div>
          </div>
          <div style="display:flex;gap:0.6rem;">
            <button class="btn btn-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Cetak / Simpan PDF</button>
            <button class="btn btn-primary" onclick="App.renderAppLayout('dashboard')"><i class="fa-solid fa-house"></i> Kembali ke Dashboard</button>
          </div>
        </div>

        <!-- Hero Scorecard -->
        <div class="review-score-hero">
          <div style="display:inline-block;padding:0.4rem 1.25rem;border-radius:999px;background:rgba(0,242,254,0.15);color:var(--cyan);font-weight:800;font-size:0.85rem;margin-bottom:1rem;border:1px solid rgba(0,242,254,0.3);">
            ${isPass ? '<i class="fa-solid fa-award"></i> SELAMAT! ANDA LULUS' : '<i class="fa-solid fa-triangle-exclamation"></i> PERLU PENINGKATAN / REMEDIAL'}
          </div>
          <div style="font-size:0.85rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;font-weight:700;">NILAI AKHIR ANDA</div>
          <div style="font-family:var(--font-head);font-size:4.5rem;font-weight:900;line-height:1;color:var(--cyan);margin:0.5rem 0;">
            ${score}
          </div>
          <div style="font-size:1.1rem;font-weight:700;color:${predicateColor};margin-bottom:1.5rem;">
            Predikat: ${predicate}
          </div>

          <!-- Quick Stats Grid -->
          <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:0.75rem;max-width:650px;margin:0 auto;">
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--r-md);padding:0.75rem;">
              <div style="font-size:0.72rem;color:var(--text-muted);font-weight:700;display:flex;align-items:center;justify-content:center;gap:0.35rem;">
                <i class="fa-solid fa-list-ol"></i> TOTAL SOAL
              </div>
              <div style="font-size:1.3rem;font-weight:800;color:var(--text-main);">${totalQ}</div>
            </div>
            <div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.3);border-radius:var(--r-md);padding:0.75rem;">
              <div style="font-size:0.72rem;color:#10b981;font-weight:700;display:flex;align-items:center;justify-content:center;gap:0.35rem;">
                <i class="fa-solid fa-circle-check"></i> BENAR / VALID
              </div>
              <div style="font-size:1.3rem;font-weight:800;color:#10b981;">${correctQ} Soal</div>
            </div>
            <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);border-radius:var(--r-md);padding:0.75rem;">
              <div style="font-size:0.72rem;color:#ef4444;font-weight:700;display:flex;align-items:center;justify-content:center;gap:0.35rem;">
                <i class="fa-solid fa-circle-xmark"></i> SALAH / KOSONG
              </div>
              <div style="font-size:1.3rem;font-weight:800;color:#ef4444;">${wrongQ} Soal</div>
            </div>
            <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.3);border-radius:var(--r-md);padding:0.75rem;">
              <div style="font-size:0.72rem;color:#f59e0b;font-weight:700;display:flex;align-items:center;justify-content:center;gap:0.35rem;">
                <i class="fa-solid fa-triangle-exclamation"></i> PELANGGARAN
              </div>
              <div style="font-size:1.3rem;font-weight:800;color:#f59e0b;">${violations}x</div>
            </div>
          </div>
        </div>

        <!-- Filter bar -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.5rem;">
          <div style="font-weight:800;font-size:1.15rem;color:var(--text-main);display:flex;align-items:center;gap:0.45rem;">
            <i class="fa-solid fa-book-open"></i> Evaluasi & Pembahasan Mendalam Soal
          </div>
          <div style="display:flex;gap:0.4rem;">
            <button class="btn btn-sm btn-outline active" id="btn-filter-all" onclick="App.filterReviewQuestions('all')">
              <i class="fa-solid fa-table-cells"></i> Semua (${totalQ})
            </button>
            <button class="btn btn-sm btn-outline" id="btn-filter-correct" onclick="App.filterReviewQuestions('correct')">
              <i class="fa-solid fa-circle-check" style="color:var(--emerald);"></i> Tepat / Valid (${correctQ})
            </button>
            <button class="btn btn-sm btn-outline" id="btn-filter-wrong" onclick="App.filterReviewQuestions('wrong')">
              <i class="fa-solid fa-circle-xmark" style="color:var(--danger);"></i> Salah / Belum (${wrongQ})
            </button>
          </div>
        </div>

        <!-- Question items list -->
        <div id="review-questions-list">
          ${questions.map((q, idx) => {
            const isEssay = (q.type === 'essay');
            const isCor = (q.is_correct == 1);
            const userAns = q.selected_option || '—';
            const corAns = q.correct_option || '—';
            const corText = q['option_' + corAns.toLowerCase()] || '';
            const essayResp = (q.essay_response || '').trim();
            const essayKey = (q.essay_answer || '').trim();
            const explanation = (q.explanation || '').trim();

            return `
              <div class="review-q-card ${isCor ? 'is-correct' : 'is-wrong'}" data-status="${isCor ? 'correct' : 'wrong'}">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                  <div style="display:flex;align-items:center;gap:0.5rem;">
                    <span style="font-size:0.85rem;font-weight:800;color:var(--text-main);background:var(--bg-canvas);border:1px solid var(--border);padding:0.25rem 0.6rem;border-radius:var(--r-sm);">
                      SOAL ${idx + 1}
                    </span>
                    <span class="badge ${isEssay ? 'badge-amber' : (isCor ? 'badge-emerald' : 'badge-danger')}">
                      ${isEssay 
                        ? (essayResp ? '<i class="fa-solid fa-pen-nib"></i> Jawaban Essai Diserahkan' : '<i class="fa-solid fa-triangle-exclamation"></i> Essai Tidak Diisi')
                        : (isCor ? '<i class="fa-solid fa-circle-check"></i> Jawaban Benar (+10 Poin)' : (userAns === '—' ? '<i class="fa-regular fa-circle"></i> Tidak Dijawab (0 Poin)' : '<i class="fa-solid fa-circle-xmark"></i> Jawaban Salah (0 Poin)'))}
                    </span>
                    <span class="badge badge-muted">
                      <i class="fa-solid ${isEssay ? 'fa-align-left' : 'fa-list-check'}"></i> ${isEssay ? 'Tipe: Essai / Uraian' : 'Tipe: Pilihan Ganda'}
                    </span>
                  </div>
                  <div style="font-size:0.8rem;color:var(--text-muted);font-weight:600;">
                    <i class="fa-solid fa-star"></i> Poin: ${isCor ? (q.points || 10) : 0} / ${q.points || 10}
                  </div>
                </div>

                <div style="font-size:1.02rem;font-weight:600;color:var(--text-main);margin-bottom:1rem;line-height:1.5;">
                  ${this.escHtml(q.question_text)}
                </div>

                ${isEssay ? `
                  <!-- Essay Student Answer View -->
                  <div class="essay-student-response-box">
                    <div style="font-weight:700;font-size:0.85rem;color:var(--text-main);margin-bottom:0.4rem;display:flex;align-items:center;gap:0.4rem;">
                      <i class="fa-solid fa-user-pen" style="color:var(--cyan);"></i> Jawaban Siswa:
                    </div>
                    <div style="white-space:pre-wrap;color:var(--text-main);font-size:0.92rem;background:var(--bg-card);padding:0.85rem 1rem;border-radius:var(--r-sm);border:1px solid var(--border-subtle);line-height:1.6;">
                      ${essayResp ? this.escHtml(essayResp) : '<span style="color:var(--text-muted);font-style:italic;">(Siswa tidak mengisi jawaban pada soal uraian ini)</span>'}
                    </div>
                  </div>

                  <!-- Essay Teacher Key Rubric -->
                  ${essayKey ? `
                    <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:var(--r-md);padding:0.85rem 1.1rem;margin-bottom:0.75rem;">
                      <div style="font-weight:700;font-size:0.85rem;color:#d97706;margin-bottom:0.35rem;display:flex;align-items:center;gap:0.4rem;">
                        <i class="fa-solid fa-list-check"></i> Rubrik & Pedoman Kunci Jawaban:
                      </div>
                      <div style="font-size:0.88rem;color:var(--text-main);white-space:pre-wrap;line-height:1.6;">${this.escHtml(essayKey)}</div>
                    </div>
                  ` : ''}

                  <!-- Essay Conceptual Explanation -->
                  <div class="review-explanation-card">
                    <div style="font-weight:700;color:var(--cyan);margin-bottom:0.45rem;display:flex;align-items:center;gap:0.5rem;font-size:0.92rem;">
                      <i class="fa-solid fa-lightbulb"></i> Pembahasan & Penjelasan Konseptual:
                    </div>
                    <div style="color:var(--text-main);font-size:0.88rem;line-height:1.6;margin-bottom:0.5rem;">
                      ${explanation ? this.escHtml(explanation) : this.generateEssayExplanation(q)}
                    </div>
                    <div style="margin-top:0.6rem;padding-top:0.6rem;border-top:1px dashed var(--border-subtle);font-size:0.8rem;color:var(--text-muted);display:flex;align-items:center;gap:0.45rem;">
                      <i class="fa-solid fa-circle-info"></i> Catatan Evaluasi: Jawaban uraian siswa dinilai secara komprehensif berdasarkan penguasaan materi, struktur nalar, dan ketepatan argumen sesuai pedoman di atas.
                    </div>
                  </div>
                ` : `
                  <!-- Multiple Choice Options Review -->
                  <div style="margin-bottom:0.75rem;">
                    ${['A','B','C','D'].map(k => {
                      const optText = q['option_' + k.toLowerCase()];
                      if (!optText) return '';
                      const isKeyCorrect = (k === corAns);
                      const isUserPick = (k === userAns);

                      let itemClass = 'review-opt-item';
                      let badgeHtml = '';

                      if (isKeyCorrect) {
                        itemClass += ' opt-correct';
                        badgeHtml = `<span class="badge badge-emerald" style="margin-left:auto;flex-shrink:0;"><i class="fa-solid fa-check"></i> Kunci Jawaban Benar</span>`;
                      }
                      if (isUserPick && !isKeyCorrect) {
                        itemClass += ' opt-student-wrong';
                        badgeHtml = `<span class="badge badge-danger" style="margin-left:auto;flex-shrink:0;"><i class="fa-solid fa-xmark"></i> Jawaban Anda</span>`;
                      } else if (isUserPick && isKeyCorrect) {
                        badgeHtml = `<span class="badge badge-emerald" style="margin-left:auto;flex-shrink:0;"><i class="fa-solid fa-check-double"></i> Jawaban Anda (Tepat)</span>`;
                      }

                      return `
                        <div class="${itemClass}">
                          <div class="review-opt-key">${k}</div>
                          <div style="flex:1;">${this.escHtml(optText)}</div>
                          ${badgeHtml}
                        </div>
                      `;
                    }).join('')}
                  </div>

                  <!-- Multiple Choice Deep Pedagogical Explanation -->
                  <div class="review-explanation-card">
                    <div style="font-weight:700;color:${isCor ? 'var(--emerald)' : 'var(--cyan)'};margin-bottom:0.45rem;display:flex;align-items:center;gap:0.5rem;font-size:0.92rem;">
                      <i class="fa-solid fa-lightbulb"></i> Pembahasan & Penjelasan Soal:
                    </div>
                    <div style="color:var(--text-main);font-size:0.88rem;line-height:1.6;margin-bottom:0.6rem;">
                      ${explanation ? this.escHtml(explanation) : this.generateMCExplanation(q, isCor, userAns, corAns, corText)}
                    </div>
                    <div style="background:var(--bg-canvas);border:1px solid var(--border-subtle);border-radius:var(--r-sm);padding:0.65rem 0.9rem;font-size:0.83rem;color:var(--text-sub);">
                      <div style="display:flex;align-items:flex-start;gap:0.5rem;margin-bottom:0.35rem;">
                        <i class="fa-solid fa-circle-check" style="color:var(--emerald);margin-top:2px;"></i>
                        <div><b>Kunci Jawaban:</b> Opsi <b>${corAns}</b> &mdash; <i>"${this.escHtml(corText)}"</i></div>
                      </div>
                      <div style="display:flex;align-items:flex-start;gap:0.5rem;">
                        <i class="fa-solid ${isCor ? 'fa-check' : 'fa-xmark'}" style="color:${isCor ? 'var(--emerald)' : 'var(--danger)'};margin-top:2px;"></i>
                        <div>
                          ${isCor 
                            ? `<span style="color:var(--emerald);font-weight:600;">Evaluasi:</span> Jawaban Anda <b>(Opsi ${userAns})</b> sudah tepat dan mencerminkan pemahaman materi yang baik.`
                            : (userAns === '—'
                                ? `<span style="color:var(--amber);font-weight:600;">Evaluasi:</span> Soal ini tidak dijawab saat ujian. Pelajari uraian pembahasan di atas untuk memahami materinya.`
                                : `<span style="color:var(--danger);font-weight:600;">Evaluasi:</span> Anda memilih <b>Opsi ${userAns}</b> ("${this.escHtml(q['option_' + userAns.toLowerCase()] || '')}"). Opsi ini belum tepat karena tidak memenuhi rumusan konsep yang ditanyakan.`)}
                        </div>
                      </div>
                    </div>
                  </div>
                `}

              </div>
            `;
          }).join('')}
        </div>

        <div style="text-align:center;margin-top:2rem;">
          <button class="btn btn-primary btn-lg" onclick="App.renderAppLayout('dashboard')">
            <i class="fa-solid fa-house"></i> Selesai & Kembali ke Dashboard
          </button>
        </div>

      </div>
    `);
  },
REPLACE;

if (strpos($code, $targetReview) !== false) {
    $code = str_replace($targetReview, $replaceReview, $code);
    echo "4. Replaced renderExamReview successfully.\n";
} else {
    echo "Error: targetReview not found!\n";
}

// 6. Update question creation modal & handler in Guru/Admin
$targetAddQModal = <<<'TARGET'
        <div id="q-essay-group" style="display:none;" class="mb-2">
          <div class="form-group"><label class="form-label">Pedoman Penilaian / Kunci Jawaban Essai</label>
            <textarea id="q-essay-ans" class="form-control" rows="3" placeholder="Kriteria jawaban benar untuk panduan penilaian guru…"></textarea>
          </div>
        </div>
        <div class="form-group mb-3"><label class="form-label">Bobot Poin</label>
TARGET;

$replaceAddQModal = <<<'REPLACE'
        <div id="q-essay-group" style="display:none;" class="mb-2">
          <div class="form-group"><label class="form-label">Pedoman Penilaian / Kunci Jawaban Essai</label>
            <textarea id="q-essay-ans" class="form-control" rows="3" placeholder="Kriteria jawaban benar untuk panduan penilaian guru…"></textarea>
          </div>
        </div>
        <div class="form-group mb-2">
          <label class="form-label"><i class="fa-solid fa-lightbulb"></i> Pembahasan & Penjelasan Soal (Evaluasi)</label>
          <textarea id="q-explanation" class="form-control" rows="3" placeholder="Masukkan uraian penjelasan/pembahasan lengkap mengapa jawaban tersebut benar untuk dibaca siswa saat evaluasi ujian…"></textarea>
        </div>
        <div class="form-group mb-3"><label class="form-label">Bobot Poin</label>
REPLACE;

if (strpos($code, $targetAddQModal) !== false) {
    $code = str_replace($targetAddQModal, $replaceAddQModal, $code);
    echo "5. Updated openAddQuestionModal successfully.\n";
} else {
    echo "Error: targetAddQModal not found!\n";
}

$targetAddQHandler = <<<'TARGET'
    const body = {
      action: 'create',
      quiz_id: quizId,
      type: type,
      question_text: document.getElementById('q-text').value,
      points: document.getElementById('q-points').value || 1
    };
TARGET;

$replaceAddQHandler = <<<'REPLACE'
    const body = {
      action: 'create',
      quiz_id: quizId,
      type: type,
      question_text: document.getElementById('q-text').value,
      points: document.getElementById('q-points').value || 1,
      explanation: document.getElementById('q-explanation')?.value || ''
    };
REPLACE;

if (strpos($code, $targetAddQHandler) !== false) {
    $code = str_replace($targetAddQHandler, $replaceAddQHandler, $code);
    echo "6. Updated handleAddQuestion successfully.\n";
} else {
    echo "Error: targetAddQHandler not found!\n";
}

// 7. Update question edit modal & handler
$targetEditQModal = <<<'TARGET'
            <div id="eq-essay-group" style="${!isEssay ? 'display:none;' : ''}" class="mb-2">
              <div class="form-group"><label class="form-label">Pedoman Penilaian / Kunci Jawaban Essai</label>
                <textarea id="eq-essay-ans" class="form-control" rows="3">${this.escHtml(q.essay_answer || '')}</textarea>
              </div>
            </div>
            <div class="form-group mb-3"><label class="form-label">Bobot Poin</label>
TARGET;

$replaceEditQModal = <<<'REPLACE'
            <div id="eq-essay-group" style="${!isEssay ? 'display:none;' : ''}" class="mb-2">
              <div class="form-group"><label class="form-label">Pedoman Penilaian / Kunci Jawaban Essai</label>
                <textarea id="eq-essay-ans" class="form-control" rows="3">${this.escHtml(q.essay_answer || '')}</textarea>
              </div>
            </div>
            <div class="form-group mb-2">
              <label class="form-label"><i class="fa-solid fa-lightbulb"></i> Pembahasan & Penjelasan Soal (Evaluasi)</label>
              <textarea id="eq-explanation" class="form-control" rows="3" placeholder="Masukkan uraian penjelasan/pembahasan lengkap untuk evaluasi siswa…">${this.escHtml(q.explanation || '')}</textarea>
            </div>
            <div class="form-group mb-3"><label class="form-label">Bobot Poin</label>
REPLACE;

if (strpos($code, $targetEditQModal) !== false) {
    $code = str_replace($targetEditQModal, $replaceEditQModal, $code);
    echo "7. Updated openEditQuestionModal successfully.\n";
} else {
    echo "Error: targetEditQModal not found!\n";
}

$targetEditQHandler = <<<'TARGET'
    const body = {
      action: 'update',
      id: qId,
      type: type,
      question_text: document.getElementById('eq-text').value,
      points: document.getElementById('eq-points').value || 1
    };
TARGET;

$replaceEditQHandler = <<<'REPLACE'
    const body = {
      action: 'update',
      id: qId,
      type: type,
      question_text: document.getElementById('eq-text').value,
      points: document.getElementById('eq-points').value || 1,
      explanation: document.getElementById('eq-explanation')?.value || ''
    };
REPLACE;

if (strpos($code, $targetEditQHandler) !== false) {
    $code = str_replace($targetEditQHandler, $replaceEditQHandler, $code);
    echo "8. Updated handleEditQuestion successfully.\n";
} else {
    echo "Error: targetEditQHandler not found!\n";
}

// 8. Update _toast function to use Font Awesome and clean up emoji prefixes
$targetToast = <<<'TARGET'
  _toast(msg, type = 'success') {
    const existing = document.getElementById('app-toast');
    if (existing) existing.remove();
    const t = document.createElement('div');
    t.id = 'app-toast';
    t.style.cssText = `position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;padding:0.85rem 1.4rem;
      background:${type === 'success' ? 'var(--emerald)' : 'var(--danger)'};
      color:#fff;border-radius:var(--r-lg);font-size:0.88rem;font-weight:600;
      box-shadow:0 6px 24px rgba(0,0,0,0.25);animation:fadeInUp 0.3s ease;`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
  },
TARGET;

$replaceToast = <<<'REPLACE'
  _toast(msg, type = 'success') {
    const existing = document.getElementById('app-toast');
    if (existing) existing.remove();
    const t = document.createElement('div');
    t.id = 'app-toast';
    t.style.cssText = `position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;padding:0.85rem 1.4rem;
      background:${type === 'success' ? 'var(--emerald)' : 'var(--danger)'};
      color:#fff;border-radius:var(--r-lg);font-size:0.88rem;font-weight:600;
      box-shadow:0 6px 24px rgba(0,0,0,0.25);animation:fadeInUp 0.3s ease;display:flex;align-items:center;gap:0.5rem;`;
    const cleanMsg = String(msg).replace(/^[\u{1F300}-\u{1F9FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}\s]+/u, '');
    const iconClass = type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark';
    t.innerHTML = `<i class="fa-solid ${iconClass}"></i> <span>${cleanMsg}</span>`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
  },
REPLACE;

if (strpos($code, $targetToast) !== false) {
    $code = str_replace($targetToast, $replaceToast, $code);
    echo "9. Updated _toast successfully.\n";
} else {
    echo "Error: targetToast not found!\n";
}

file_put_contents($file, $code);
echo "Core exam & review logic successfully updated in app.js!\n";

