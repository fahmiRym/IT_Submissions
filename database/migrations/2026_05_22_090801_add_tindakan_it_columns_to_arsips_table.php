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
            $table->string('tindakan_in')->nullable()->after('catatan_it');
            $table->text('ket_tindakan_in')->nullable()->after('tindakan_in');
            $table->string('tindakan_out')->nullable()->after('ket_tindakan_in');
            $table->text('ket_tindakan_out')->nullable()->after('tindakan_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->dropColumn(['tindakan_in', 'ket_tindakan_in', 'tindakan_out', 'ket_tindakan_out']);
        });
    }
};
