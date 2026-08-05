<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('arsip_shares', function (Blueprint $t) {
            $t->timestamp('read_at')->nullable()->after('note');
            $t->timestamp('noted_at')->nullable()->after('read_at');
            $t->text('note_content')->nullable()->after('noted_at');
            // Array of {no_transaksi, approved_at, approved_name}
            // supaya satu shared user bisa approve multiple no_transaksi tertentu.
            $t->json('approvals_json')->nullable()->after('note_content');
        });
    }

    public function down(): void
    {
        Schema::table('arsip_shares', function (Blueprint $t) {
            $t->dropColumn(['read_at', 'noted_at', 'note_content', 'approvals_json']);
        });
    }
};
