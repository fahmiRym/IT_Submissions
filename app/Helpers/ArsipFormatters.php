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
