<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Delegation TTD: kabag → SPV, manager → assistant, dst.
 *
 * Tambah 2 lapis:
 * 1. Persistent — users.delegate_to_id (+ window) supaya user set "kalau saya cuti,
 *    forward ke user X" sekali di profile.
 * 2. Snapshot per-pengajuan — arsip_approvals.delegated_from_id & arsip_signatures.delegated_from_id
 *    supaya render TTD di draft/verify tahu "Kabag diwakilkan oleh SPV Fulan".
 */
return new class extends Migration {
    public function up(): void
    {
        // 1) Persistent delegation di user profile
        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('delegate_to_id')->nullable()->after('signature_path')
                ->constrained('users')->nullOnDelete();
            $t->date('delegate_active_from')->nullable()->after('delegate_to_id');
            $t->date('delegate_active_until')->nullable()->after('delegate_active_from');
            $t->string('delegate_reason', 200)->nullable()->after('delegate_active_until');
        });

        // 2) Snapshot per-pengajuan di approval step
        Schema::table('arsip_approvals', function (Blueprint $t) {
            // Original approver yg didelegasikan (kalau approver_id != user asli)
            $t->foreignId('delegated_from_id')->nullable()->after('approver_id')
                ->constrained('users')->nullOnDelete();
            $t->index('delegated_from_id');
        });

        // 3) Snapshot di record TTD (utk render draft & verify)
        Schema::table('arsip_signatures', function (Blueprint $t) {
            $t->foreignId('delegated_from_id')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('arsip_signatures', function (Blueprint $t) {
            $t->dropConstrainedForeignId('delegated_from_id');
        });
        Schema::table('arsip_approvals', function (Blueprint $t) {
            $t->dropIndex(['delegated_from_id']);
            $t->dropConstrainedForeignId('delegated_from_id');
        });
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['delegate_reason', 'delegate_active_until', 'delegate_active_from']);
            $t->dropConstrainedForeignId('delegate_to_id');
        });
    }
};
