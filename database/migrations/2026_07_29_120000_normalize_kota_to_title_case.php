<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Normalize kolom kota di branches + setting kota_ba dari ALL CAPS
     * ke Title Case (PASURUAN → Pasuruan). One-shot data cleanup.
     */
    public function up(): void
    {
        // branches.kota
        DB::table('branches')->get(['id', 'kota'])->each(function ($row) {
            $normalized = Str::title(trim(strtolower($row->kota ?? '')));
            if ($normalized !== $row->kota) {
                DB::table('branches')->where('id', $row->id)->update([
                    'kota'       => $normalized,
                    'updated_at' => now(),
                ]);
            }
        });

        // settings.kota_ba (single row)
        $s = DB::table('settings')->where('key', 'kota_ba')->first();
        if ($s) {
            $normalized = Str::title(trim(strtolower($s->value ?? '')));
            if ($normalized !== $s->value) {
                DB::table('settings')->where('key', 'kota_ba')->update([
                    'value'      => $normalized,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Non-reversible — data cleanup. Kalau perlu kembali uppercase,
        // set manual via superadmin/settings.
    }
};
