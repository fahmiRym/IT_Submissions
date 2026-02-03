<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->string('jenis_pengajuan', 30)
                  ->after('no_registrasi')
                  ->default('cancel');
        });
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->dropColumn('jenis_pengajuan');
        });
    }
};

