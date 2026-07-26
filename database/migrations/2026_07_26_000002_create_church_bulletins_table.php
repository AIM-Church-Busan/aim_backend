<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Policy:
 * - "upload date" / "last modified date" are covered by Laravel's built-in
 *   created_at / updated_at timestamps — no separate date columns needed.
 * - pdf_path stores only the disk-relative path (never a full URL), so
 *   switching disks later (public -> R2) doesn't require a data migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_bulletins', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('pdf_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('church_bulletins');
    }
};
