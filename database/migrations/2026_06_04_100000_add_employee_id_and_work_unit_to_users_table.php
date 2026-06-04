<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'employee_id')) {
                $table->string('employee_id', 20)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('users', 'work_unit_id')) {
                $table->unsignedBigInteger('work_unit_id')->nullable()->after('department_id');
                $table->foreign('work_unit_id')->references('id')->on('units')->nullOnDelete();
                $table->index('work_unit_id', 'idx_users_workunit');
            }
            if (!Schema::hasColumn('users', 'odoo_user_id')) {
                $table->integer('odoo_user_id')->nullable()->after('work_unit_id');
            }
            if (!Schema::hasColumn('users', 'source')) {
                $table->enum('source', ['legacy', 'hr_import', 'manual'])
                    ->default('legacy')
                    ->after('is_active');
            }
            if (!Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('source');
            }
            if (!Schema::hasColumn('users', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('must_change_password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'work_unit_id')) {
                $table->dropForeign(['work_unit_id']);
                $table->dropIndex('idx_users_workunit');
                $table->dropColumn('work_unit_id');
            }
            foreach (['employee_id', 'odoo_user_id', 'source', 'must_change_password', 'last_synced_at'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
