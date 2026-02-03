<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
        $table->engine = 'InnoDB';
        $table->id();
        $table->string('name');
        $table->string('username',100)->nullable()->unique();
        $table->string('email')->nullable()->unique();
        $table->string('password')->nullable();
        $table->enum('role',['admin','superadmin'])->default('admin');
        $table->foreignId('department_id')->nullable()
              ->constrained()->nullOnDelete();
        $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

