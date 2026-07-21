<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table untuk kelola versi APK Android (auto-update mechanism).
 *
 * Satu row per app_slug — mis. `itsubmissions`, `itapproval`, `itasistant`.
 * Android app POLL `GET /api/mobile/version?app={slug}` saat startup.
 * Kalau `version_code` di server > BuildConfig.VERSION_CODE di app → prompt update.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_versions', function (Blueprint $t) {
            $t->id();
            $t->string('app_slug', 40)->unique()->comment('itsubmissions | itapproval | itasistant | dst');
            $t->string('app_name', 100)->comment('Nama tampil, mis. "IT Submissions"');
            $t->string('latest_version', 20)->comment('Semver mis. "1.2.3"');
            $t->unsignedInteger('version_code')->comment('android versionCode int');
            $t->string('apk_path')->nullable()->comment('relative path di storage/app/public/apk/');
            $t->string('apk_url_override')->nullable()->comment('URL absolut kalau APK di-host di CDN eksternal');
            $t->boolean('force_update')->default(false)->comment('Kalau true, app WAJIB update sebelum dipakai');
            $t->text('changelog')->nullable();
            $t->unsignedBigInteger('file_size')->nullable();
            $t->string('file_hash', 64)->nullable()->comment('sha256 untuk verify integrity');
            $t->unsignedBigInteger('uploaded_by')->nullable();
            $t->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
