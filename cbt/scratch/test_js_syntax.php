<?php
$js = file_get_contents('c:/xampp/htdocs/moodle/cbt/js/app.js');
$chars = ['{' => '}', '(' => ')', '[' => ']'];
$stack = [];
$len = strlen($js);
$inString = false;
$stringChar = null;
$escape = false;

for ($i = 0; $i < $len; $i++) {
    $c = $js[$i];
    if ($escape) {
        $escape = false;
        continue;
    }
    if ($c === '\\') {
        $escape = true;
        continue;
    }
    if ($inString) {
        if ($c === $stringChar) {
            $inString = false;
            $stringChar = null;
        }
        continue;
    }
    if ($c === '"' || $c === "'" || $c === '`') {
        $inString = true;
        $stringChar = $c;
        continue;
    }
    if ($c === '{' || $c === '(' || $c === '[') {
        $stack[] = [$c, $i];
    } elseif ($c === '}' || $c === ')' || $c === ']') {
        if (empty($stack)) {
            echo "Unexpected closing $c at offset $i\n";
            exit;
        }
        $last = array_pop($stack);
        if ($chars[$last[0]] !== $c) {
            echo "Mismatched {$last[0]} at {$last[1]} with $c at $i\n";
            exit;
        }
    }
}
if (!empty($stack)) {
    echo "Unclosed brackets count: " . count($stack) . ". First unclosed: " . $stack[0][0] . " at " . $stack[0][1] . "\n";
} else {
    echo "Bracket pairing test passed with 0 unclosed brackets!\n";
}

