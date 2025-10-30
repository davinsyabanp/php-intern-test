<?php

// Usage: php x_o_pattern.php [N]
// Mencetak pola matriks N x N berisi 'X' pada kedua diagonal, selainnya 'O'.

$nArg = $argv[1] ?? null;
$n = is_numeric($nArg) ? (int)$nArg : 7; // default 7 jika tidak diberikan

if ($n <= 0) {
    fwrite(STDERR, "N harus bilangan bulat positif.\n");
    exit(1);
}

for ($i = 1; $i <= $n; $i++) {
    $row = [];
    for ($j = 1; $j <= $n; $j++) {
        $isMainDiagonal = ($i === $j);
        $isAntiDiagonal = ($i + $j === $n + 1);
        $row[] = ($isMainDiagonal || $isAntiDiagonal) ? 'X' : 'O';
    }
    echo implode(' ', $row) . PHP_EOL;
}


