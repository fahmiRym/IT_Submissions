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
            $table->enum('ba', ['Process', 'Done', 'Void', 'None'])->default('Process')->change();
            $table->enum('arsip', ['Pending', 'Process', 'Done', 'None'])->default('Pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->enum('ba', ['Process', 'Done'])->default('Process')->change();
            $table->enum('arsip', ['Process', 'Done'])->default('Process')->change();
        });
    }
};
