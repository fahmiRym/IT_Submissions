<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    Schema::create('arsips', function (Blueprint $table) {
        $table->engine = 'InnoDB';
        $table->id();

        $table->date('tgl_pengajuan');
        $table->date('tgl_arsip')->nullable();

        $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('superadmin_id')->nullable()
              ->constrained('users')->nullOnDelete();

        $table->foreignId('department_id')->constrained()->cascadeOnDelete();
        $table->foreignId('manager_id')->constrained()->cascadeOnDelete();
        $table->foreignId('unit_id')->constrained()->cascadeOnDelete();

        $table->enum('eror',['Human','System']);
        $table->text('ket_eror')->nullable();

        $table->text('no_doc')->nullable();
        $table->text('no_transaksi')->nullable();

        $table->enum('ba',['Process','Done'])->default('Process');
        $table->enum('arsip',['Process','Done'])->default('Process');
        $table->enum('ket_process',
            ['Process','Done','Partial Done','Pending','Void']
        )->default('Process');

        $table->enum('status',
            ['Check','Process','Done','Reject','Void']
        )->default('Process');

        $table->string('bukti_scan')->nullable();

        $table->timestamps();
    });
}


    public function down(): void
    {
        Schema::dropIfExists('arsips');
    }
};


