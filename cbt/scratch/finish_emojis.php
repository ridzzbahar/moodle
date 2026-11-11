<?php
$file = 'c:/xampp/htdocs/moodle/cbt/js/app.js';
$code = file_get_contents($file);

$code = str_replace(
    '`<tr><td colspan="6"><div class="empty-state"><div class="empty-icon">🏛️</div><p>Belum ada kelas.</p></div></td></tr>`',
    '`<tr><td colspan="6"><div class="empty-state"><div class="empty-icon"><i class="fa-solid fa-building-columns"></i></div><p>Belum ada kelas.</p></div></td></tr>`',
    $code
);

$code = str_replace(
    '`<tr><td colspan="${isStaff ? 8 : 7}"><div class="empty-state"><div class="empty-icon">📅</div><p>Belum ada jadwal kelas.</p></div></td></tr>`',
    '`<tr><td colspan="${isStaff ? 8 : 7}"><div class="empty-state"><div class="empty-icon"><i class="fa-solid fa-calendar-days"></i></div><p>Belum ada jadwal kelas.</p></div></td></tr>`',
    $code
);

$code = str_replace(
    "this._toast(id ? '✅ Pengumuman diperbarui.' : '✅ Pengumuman baru dipublikasikan!');",
    "this._toast(id ? 'Pengumuman diperbarui.' : 'Pengumuman baru dipublikasikan!');",
    $code
);

file_put_contents($file, $code);
echo "Final 3 emojis updated successfully.\n";

