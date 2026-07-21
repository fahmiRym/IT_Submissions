<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','superadmin','accounting','spv','kabag','manager') DEFAULT 'admin'");
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','superadmin','accounting') DEFAULT 'admin'");
    }
};
