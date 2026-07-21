<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_pengajuan_access', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('jenis', 32); // Cancel, Adjust, Mutasi_Billet, Mutasi_Produk, Bundel, Internal_Memo
            $t->unsignedBigInteger('granted_by')->nullable();
            $t->timestamps();

            $t->unique(['user_id', 'jenis']);
            $t->index('jenis');
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // Backfill: existing user (selain superadmin) dapat akses ke semua jenis
        // supaya behavior aplikasi TIDAK berubah setelah migrasi ini jalan.
        $jenisAll = ['Cancel', 'Adjust', 'Mutasi_Billet', 'Mutasi_Produk', 'Bundel', 'Internal_Memo'];
        $users = DB::table('users')->where('role', '!=', 'superadmin')->pluck('id');
        $now = now();
        $rows = [];
        foreach ($users as $uid) {
            foreach ($jenisAll as $j) {
                $rows[] = [
                    'user_id' => $uid,
                    'jenis' => $j,
                    'granted_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('user_pengajuan_access')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_pengajuan_access');
    }
};
