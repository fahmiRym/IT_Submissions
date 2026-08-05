<?php

if (!function_exists('formatArsipText')) {
    function formatArsipText(?string $text): array
    {
        if (!$text) return [];

        // Pecah baris, bersihkan, JAGA URUTAN ASLI
        return array_values(array_filter(array_map(
            fn($line) => trim($line),
            preg_split("/\r\n|\n|\r/", $text)
        )));
    }
}

if (!function_exists('fmtOdoo')) {
    /**
     * Format nominal ala Odoo user: whole.-decimal (3 digit desimal max).
     *   65.215   → '65.-215'
     *   -65.215  → '-65.-215'
     *   59262    → '59,262.-'
     *   59262.6  → '59,262.-6'
     *   0        → '0.-'
     *   null/''  → '0.-'   (treated as zero — kolom kosong ditampilkan 0)
     * Comma = ribuan, '.-' = separator whole/decimal.
     */
    function fmtOdoo($n): string
    {
        if ($n === null || $n === '') return '0.-';
        $num = (float) $n;
        $negative = $num < 0;
        $abs = abs($num);
        $wholePart = (int) floor($abs);
        $decimalPart = $abs - $wholePart;
        $wholeFmt = number_format($wholePart, 0, '.', ',');
        if ($decimalPart == 0) {
            $result = $wholeFmt . '.-';
        } else {
            // 3 digit desimal, buang trailing zero
            $decStr = rtrim(number_format($decimalPart, 3, '.', ''), '0');
            $decDigits = ltrim($decStr, '0.');
            $result = $wholeFmt . '.-' . $decDigits;
        }
        return $negative ? '-' . $result : $result;
    }
}
