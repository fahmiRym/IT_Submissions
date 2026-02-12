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
        // 1. arsip_mutasi_items: qty
        Schema::table('arsip_mutasi_items', function (Blueprint $table) {
            $table->double('qty')->change();
        });

        // 2. arsip_adjust_items: qty_in, qty_out
        Schema::table('arsip_adjust_items', function (Blueprint $table) {
            $table->double('qty_in')->default(0)->change();
            $table->double('qty_out')->default(0)->change();
        });

        // 3. arsip_bundel_items: qty
        Schema::table('arsip_bundel_items', function (Blueprint $table) {
            $table->double('qty')->change();
        });

        // 4. arsips: total_qty_in, total_qty_out
        Schema::table('arsips', function (Blueprint $table) {
            $table->double('total_qty_in')->default(0)->change();
            $table->double('total_qty_out')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to Integer
        Schema::table('arsip_mutasi_items', function (Blueprint $table) {
            $table->integer('qty')->change();
        });

        Schema::table('arsip_adjust_items', function (Blueprint $table) {
            $table->integer('qty_in')->default(0)->change();
            $table->integer('qty_out')->default(0)->change();
        });

        Schema::table('arsip_bundel_items', function (Blueprint $table) {
            $table->integer('qty')->change();
        });

        Schema::table('arsips', function (Blueprint $table) {
            $table->integer('total_qty_in')->default(0)->change();
            $table->integer('total_qty_out')->default(0)->change();
        });
    }
};
