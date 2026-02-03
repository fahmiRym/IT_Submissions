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
            $table->renameColumn('jumlah_lot', 'jumlah_qty');
            $table->renameColumn('total_lot_in', 'total_qty_in');
            $table->renameColumn('total_lot_out', 'total_qty_out');
        });
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->renameColumn('jumlah_qty', 'jumlah_lot');
            $table->renameColumn('total_qty_in', 'total_lot_in');
            $table->renameColumn('total_qty_out', 'total_lot_out');
        });
    }
};
