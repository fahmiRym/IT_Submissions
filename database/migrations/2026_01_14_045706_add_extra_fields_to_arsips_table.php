<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->integer('jumlah_lot')->nullable()->after('jenis_pengajuan');
            $table->text('keterangan')->nullable()->after('jumlah_lot');
            $table->string('sub_jenis', 30)->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->dropColumn(['jumlah_lot', 'keterangan', 'sub_jenis']);
        });
    }
};
