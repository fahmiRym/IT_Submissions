<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Specimen TTD per user
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'signature_path')) {
                $table->string('signature_path')->nullable()->after('photo');
            }
        });

        // Token verifikasi publik per pengajuan (untuk QR)
        Schema::table('arsips', function (Blueprint $table) {
            if (!Schema::hasColumn('arsips', 'verify_token')) {
                $table->uuid('verify_token')->nullable()->unique()->after('no_registrasi');
            }
        });

        // Catatan tanda tangan digital yang diterapkan ke dokumen
        if (!Schema::hasTable('arsip_signatures')) {
            Schema::create('arsip_signatures', function (Blueprint $table) {
                $table->id();
                $table->foreignId('arsip_id')->constrained('arsips')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role_label', 50);              // Pemohon / Accounting / Departemen IT
                $table->string('signer_name', 150);            // snapshot nama saat TTD
                $table->string('signature_path')->nullable();  // snapshot specimen saat TTD
                $table->string('hash', 128)->nullable();       // sha256 integritas TTD
                $table->text('note')->nullable();
                $table->string('ip_address', 64)->nullable();
                $table->timestamp('signed_at')->nullable();
                $table->timestamps();

                $table->unique(['arsip_id', 'role_label']);    // 1 TTD per peran per dokumen
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_signatures');

        Schema::table('arsips', function (Blueprint $table) {
            if (Schema::hasColumn('arsips', 'verify_token')) {
                $table->dropUnique(['verify_token']);
                $table->dropColumn('verify_token');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'signature_path')) {
                $table->dropColumn('signature_path');
            }
        });
    }
};
