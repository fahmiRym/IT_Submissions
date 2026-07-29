<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                                  // e.g. "Kantor Pusat Pasuruan"
            $table->string('code', 20)->unique();                         // e.g. "PSR" / "JTK"
            $table->string('kota', 60);                                   // dipakai stamp dokumen: "PASURUAN"
            $table->string('alamat', 255)->nullable();                    // alamat lengkap (opsional)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
