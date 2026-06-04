<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Jabatan untuk membantu memfilter pilihan approver
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'jabatan')) {
                $table->string('jabatan', 50)->nullable()->after('role');
            }
        });

        if (!Schema::hasTable('arsip_approvals')) {
            Schema::create('arsip_approvals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('arsip_id')->constrained('arsips')->cascadeOnDelete();
                $table->unsignedInteger('step_order');
                $table->string('role_label', 50);                 // Pemohon/SPV/Kabag/Manager/Accounting/Departemen IT
                $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete(); // null = IT (any superadmin)
                $table->string('status', 20)->default('pending'); // pending / approved / rejected
                $table->text('note')->nullable();
                $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('acted_at')->nullable();
                $table->timestamps();

                $table->index(['arsip_id', 'step_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_approvals');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'jabatan')) {
                $table->dropColumn('jabatan');
            }
        });
    }
};
