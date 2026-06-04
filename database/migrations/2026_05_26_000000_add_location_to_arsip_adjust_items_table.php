<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('arsip_adjust_items', function (Blueprint $table) {
            if (!Schema::hasColumn('arsip_adjust_items', 'location')) {
                $table->string('location', 150)->nullable()->after('lot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('arsip_adjust_items', function (Blueprint $table) {
            if (Schema::hasColumn('arsip_adjust_items', 'location')) {
                $table->dropColumn('location');
            }
        });
    }
};
