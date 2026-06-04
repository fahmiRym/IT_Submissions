<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE arsips MODIFY jenis_pengajuan ENUM('Cancel','Adjust','Mutasi_Billet','Mutasi_Produk','Internal_Memo','Bundel','Produk_Baru') NOT NULL DEFAULT 'Cancel'");
    }

    public function down(): void
    {
        // Pastikan tidak ada baris yang masih bernilai 'Produk_Baru' sebelum down,
        // bila ada akan terpotong/error. Aman: kosongkan dulu.
        DB::table('arsips')->where('jenis_pengajuan', 'Produk_Baru')->update(['jenis_pengajuan' => 'Internal_Memo']);
        DB::statement("ALTER TABLE arsips MODIFY jenis_pengajuan ENUM('Cancel','Adjust','Mutasi_Billet','Mutasi_Produk','Internal_Memo','Bundel') NOT NULL DEFAULT 'Cancel'");
    }
};
