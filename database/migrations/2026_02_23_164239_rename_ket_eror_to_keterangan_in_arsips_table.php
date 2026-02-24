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
            if (Schema::hasColumn('arsips', 'ket_eror') && !Schema::hasColumn('arsips', 'keterangan')) {
                $table->renameColumn('ket_eror', 'keterangan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            if (Schema::hasColumn('arsips', 'keterangan') && !Schema::hasColumn('arsips', 'ket_eror')) {
                $table->renameColumn('keterangan', 'ket_eror');
            }
        });
    }
};
