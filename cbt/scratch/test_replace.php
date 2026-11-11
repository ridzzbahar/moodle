<?php
// cbt/scratch/test_replace.php
$file = 'c:/xampp/htdocs/moodle/cbt/js/app.js';
$code = file_get_contents($file);

// 1. In startExam: ensure this.savedEssays is initialized
$code = str_replace(
    "this.savedAnswers         = data.saved_answers || {};\n        this.flaggedQuestions     = {};",
    "this.savedAnswers         = data.saved_answers || {};\n        this.savedEssays          = data.saved_essays || {};\n        this.flaggedQuestions     = {};",
    $code
);

// 2. Add handleEssayInput, generateMCExplanation, generateEssayExplanation to App
// Let's check where selectOption is defined in app.js
$targetSelectOption = "  selectOption(qId, key) {
    this.savedAnswers[qId] = key;
    this.renderExamScreen();
    fetch('api/exam.php?action=save_answer', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ attempt_id: this.attemptId, question_id: qId, selected_option: key }) });
  },

  clearChoice(qId) {
    delete this.savedAnswers[qId];
    this.renderExamScreen();
    fetch('api/exam.php?action=save_answer', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ attempt_id: this.attemptId, question_id: qId, selected_option: '' }) });
  },";

$replacementSelectOption = "  selectOption(qId, key) {
    this.savedAnswers[qId] = key;
    this.renderExamScreen();
    fetch('api/exam.php?action=save_answer', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ attempt_id: this.attemptId, question_id: qId, selected_option: key }) });
  },

  handleEssayInput(qId, val) {
    if (!this.savedEssays) this.savedEssays = {};
    this.savedEssays[qId] = val;

    const countEl = document.getElementById('essay-char-count');
    if (countEl) countEl.innerHTML = `<i class=\"fa-solid fa-font\"></i> \${val.length} karakter`;

    const statusEl = document.getElementById('essay-save-status');
    if (statusEl) statusEl.innerHTML = `<i class=\"fa-solid fa-circle-notch fa-spin\"></i> Menyimpan...`;

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
        if (statusEl) statusEl.innerHTML = `<i class=\"fa-solid fa-circle-check\" style=\"color:var(--emerald);\"></i> Tersimpan`;
        const palNums = document.querySelectorAll('.palette-num');
        if (palNums && palNums[this.currentQuestionIndex]) {
          if (val.trim().length > 0) palNums[this.currentQuestionIndex].classList.add('answered');
          else palNums[this.currentQuestionIndex].classList.remove('answered');
        }
      }).catch(() => {
        if (statusEl) statusEl.innerHTML = `<i class=\"fa-solid fa-triangle-exclamation\" style=\"color:var(--danger);\"></i> Gagal menyimpan`;
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
      if (countEl) countEl.innerHTML = `<i class=\"fa-solid fa-font\"></i> 0 karakter`;
      const statusEl = document.getElementById('essay-save-status');
      if (statusEl) statusEl.innerHTML = ``;
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
    return `Soal ini menguji kompetensi dan pemahaman analitis siswa. Jawaban yang tepat dan valid adalah <b>Opsi \${corAns}</b> (<i>\${this.escHtml(corText)}</i>) karena telah memenuhi seluruh parameter dan prinsip ilmiah yang dirumuskan pada pertanyaan materi. Pilihan lainnya merupakan opsi distraktor yang mengandung kekeliruan atau ketidaksesuaian konteks.`;
  },

  generateEssayExplanation(q) {
    return `Soal essay ini bertujuan mengevaluasi daya nalar konseptual, struktur argumentasi, dan penguasaan materi secara menyeluruh. Penilaian dilakukan dengan meninjau kesesuaian uraian siswa terhadap rubrik acuan pengampu, ketepatan teori pendukung, serta kelengkapan penjelasan yang dipaparkan.`;
  },`;

if (strpos($code, $targetSelectOption) !== false) {
    $code = str_replace($targetSelectOption, $replacementSelectOption, $code);
    echo "Replaced selectOption & added handleEssayInput, clearChoice, generateMCExplanation, generateEssayExplanation successfully.\n";
} else {
    echo "Warning: targetSelectOption not found directly. Checking with normalized line endings...\n";
    $normCode = str_replace("\r\n", "\n", $code);
    $normTarget = str_replace("\r\n", "\n", $targetSelectOption);
    if (strpos($normCode, $normTarget) !== false) {
        $normCode = str_replace($normTarget, $replacementSelectOption, $normCode);
        $code = $normCode;
        echo "Replaced with normalized line endings successfully.\n";
    } else {
        echo "Error: targetSelectOption still not found.\n";
    }
}

file_put_contents($file, $code);

