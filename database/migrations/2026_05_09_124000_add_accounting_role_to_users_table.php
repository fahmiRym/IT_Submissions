<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Gunakan statement SQL mentah untuk mengubah ENUM di MySQL
        // Karena Laravel Blueprint->enum() tidak mendukung modifikasi ENUM yang sudah ada dengan mudah tanpa library tambahan (doctrine/dbal)
        \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'superadmin', 'accounting') DEFAULT 'admin'");
    }

    public function down(): void
    {
        // Balikkan ke semula jika rollback (hati-hati jika ada data 'accounting' akan hilang/eror)
        \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'superadmin') DEFAULT 'admin'");
    }
};
