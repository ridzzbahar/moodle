<?php
$lines = file('c:/xampp/htdocs/moodle/cbt/js/app.js');
$found = [];
foreach ($lines as $i => $line) {
    if (preg_match_all('/[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}]/u', $line, $matches)) {
        foreach ($matches[0] as $m) {
            $found[$m] = ($found[$m] ?? 0) + 1;
        }
    }
}
arsort($found);
foreach ($found as $char => $cnt) {
    printf("%s (U+%04X): %d occurrences\n", $char, mb_ord($char), $cnt);
}

