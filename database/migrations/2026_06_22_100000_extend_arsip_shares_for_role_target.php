<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // STEP 1: Drop FK kalau masih ada (sebelumnya mungkin sudah ke-drop dari attempt sebelumnya)
        $hasFk = DB::selectOne(
            "SELECT COUNT(*) as c FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_NAME = 'arsip_shares' AND CONSTRAINT_NAME = 'arsip_shares_user_id_foreign'"
        );
        if ((int) $hasFk->c > 0) {
            Schema::table('arsip_shares', function (Blueprint $t) {
                $t->dropForeign(['user_id']);
            });
        }

        // STEP 2a: Pastikan ada index plain di arsip_id sebelum drop composite unique
        // (FK arsip_id butuh covering index — composite (arsip_id, user_id) lagi men-cover-nya)
        $hasArsipIdx = DB::selectOne(
            "SELECT COUNT(*) as c FROM information_schema.STATISTICS
             WHERE TABLE_NAME = 'arsip_shares' AND INDEX_NAME = 'arsip_shares_arsip_id_index'"
        );
        if ((int) $hasArsipIdx->c === 0) {
            Schema::table('arsip_shares', function (Blueprint $t) {
                $t->index('arsip_id');
            });
        }

        // STEP 2b: Drop unique constraint lama (idempotent check)
        $hasUnique = DB::selectOne(
            "SELECT COUNT(*) as c FROM information_schema.STATISTICS
             WHERE TABLE_NAME = 'arsip_shares' AND INDEX_NAME = 'arsip_shares_arsip_id_user_id_unique'"
        );
        if ((int) $hasUnique->c > 0) {
            Schema::table('arsip_shares', function (Blueprint $t) {
                $t->dropUnique(['arsip_id', 'user_id']);
            });
        }

        // STEP 3: Tambah kolom baru + ubah user_id jadi nullable
        Schema::table('arsip_shares', function (Blueprint $t) {
            $t->string('target_type', 16)->default('user')->after('arsip_id');
            $t->string('role', 32)->nullable()->after('user_id');
            $t->unsignedBigInteger('user_id')->nullable()->change();
        });

        // STEP 4: Re-add unique constraints + index + FK
        // MySQL: multiple NULLs di-treat sebagai distinct, jadi valid untuk dua jenis target.
        Schema::table('arsip_shares', function (Blueprint $t) {
            $t->unique(['arsip_id', 'user_id'], 'arsip_shares_arsip_user_unique');
            $t->unique(['arsip_id', 'role'], 'arsip_shares_arsip_role_unique');
            $t->index('role');
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('arsip_shares', function (Blueprint $t) {
            $t->dropForeign(['user_id']);
            $t->dropUnique('arsip_shares_arsip_user_unique');
            $t->dropUnique('arsip_shares_arsip_role_unique');
            $t->dropIndex(['role']);
            $t->dropColumn(['target_type', 'role']);

            // user_id balik jadi NOT NULL (hapus row role-based dulu kalau ada)
            DB::table('arsip_shares')->whereNull('user_id')->delete();
            $t->unsignedBigInteger('user_id')->nullable(false)->change();
            $t->unique(['arsip_id', 'user_id']);
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
