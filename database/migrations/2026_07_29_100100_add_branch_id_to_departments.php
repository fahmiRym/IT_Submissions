<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branches')->nullOnDelete();
        });

        // Seed 1 default branch "Pasuruan" (kalau belum ada) + backfill semua dept ke branch itu
        $existing = DB::table('branches')->where('code', 'PSR')->first();
        if (!$existing) {
            $id = DB::table('branches')->insertGetId([
                'name'       => 'Kantor Pusat Pasuruan',
                'code'       => 'PSR',
                'kota'       => 'PASURUAN',
                'alamat'     => null,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $id = $existing->id;
        }

        // Backfill: semua dept yang belum ada branch → assign ke default
        DB::table('departments')->whereNull('branch_id')->update(['branch_id' => $id]);
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
