<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            if (!Schema::hasColumn('arsips', 'scan_final')) {
                $table->string('scan_final')->nullable()->after('scan_ba_accounting');
            }
        });
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            if (Schema::hasColumn('arsips', 'scan_final')) {
                $table->dropColumn('scan_final');
            }
        });
    }
};
