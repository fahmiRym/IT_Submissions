<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename columns safely
        Schema::table('arsips', function (Blueprint $table) {
            // Rename 'keterangan' first to free up the name
            if (Schema::hasColumn('arsips', 'keterangan') && !Schema::hasColumn('arsips', 'catatan_tambahan')) {
                $table->renameColumn('keterangan', 'catatan_tambahan');
            }
            
            // Now rename 'ket_eror' to 'keterangan'
            if (Schema::hasColumn('arsips', 'ket_eror') && !Schema::hasColumn('arsips', 'keterangan')) {
                $table->renameColumn('ket_eror', 'keterangan');
            }

            if (Schema::hasColumn('arsips', 'eror')) {
                $table->renameColumn('eror', 'kategori');
            }

            if (Schema::hasColumn('arsips', 'jumlah_qty')) {
                $table->renameColumn('jumlah_qty', 'target_qty');
            }
        });

        // 2. Reorder columns using Raw SQL (MySQL specific)
        // We use try-catch or just execute if we are sure about the driver
        try {
            DB::statement("ALTER TABLE arsips MODIFY COLUMN kategori ENUM('Human', 'System', 'None') AFTER tgl_arsip");
            DB::statement("ALTER TABLE arsips MODIFY COLUMN keterangan TEXT AFTER kategori");
            DB::statement("ALTER TABLE arsips MODIFY COLUMN no_doc TEXT AFTER keterangan");
            DB::statement("ALTER TABLE arsips MODIFY COLUMN no_transaksi TEXT AFTER no_doc");
            DB::statement("ALTER TABLE arsips MODIFY COLUMN target_qty INT AFTER no_transaksi");
            DB::statement("ALTER TABLE arsips MODIFY COLUMN total_qty_in INT AFTER target_qty");
            DB::statement("ALTER TABLE arsips MODIFY COLUMN total_qty_out INT AFTER total_qty_in");
            DB::statement("ALTER TABLE arsips MODIFY COLUMN detail_barang JSON AFTER total_qty_out");
            DB::statement("ALTER TABLE arsips MODIFY COLUMN sub_jenis VARCHAR(255) AFTER detail_barang");
            DB::statement("ALTER TABLE arsips MODIFY COLUMN catatan_tambahan VARCHAR(255) AFTER sub_jenis");
        } catch (\Exception $e) {
            // Silently fail reorder if not MySQL or other issues, optional
        }
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            if (Schema::hasColumn('arsips', 'kategori')) $table->renameColumn('kategori', 'eror');
            if (Schema::hasColumn('arsips', 'keterangan')) $table->renameColumn('keterangan', 'ket_eror');
            if (Schema::hasColumn('arsips', 'target_qty')) $table->renameColumn('target_qty', 'jumlah_qty');
            if (Schema::hasColumn('arsips', 'catatan_tambahan')) $table->renameColumn('catatan_tambahan', 'keterangan');
        });
    }
};
