<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users_staging', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->string('name', 150);
            $table->string('department_name', 150)->nullable();
            $table->string('work_unit_name', 150)->nullable();

            $table->unsignedBigInteger('matched_user_id')->nullable();
            $table->enum('match_method', ['exact_name', 'fuzzy_name', 'employee_id', 'manual', 'new'])->nullable();
            $table->unsignedTinyInteger('match_score')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'applied', 'skipped'])->default('pending');
            $table->text('notes')->nullable();

            $table->string('batch_id', 40)->index();
            $table->timestamps();

            $table->foreign('matched_user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['batch_id', 'status']);
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_staging');
    }
};
