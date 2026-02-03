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
            $table->enum('eror', ['Human', 'System', 'None'])->change();
            $table->enum('ket_process', ['Review', 'Process', 'Done', 'Partial Done', 'Pending', 'Void'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->enum('eror', ['Human', 'System'])->change();
            $table->enum('ket_process', ['Process', 'Done', 'Partial Done', 'Pending', 'Void'])->change();
        });
    }
};
