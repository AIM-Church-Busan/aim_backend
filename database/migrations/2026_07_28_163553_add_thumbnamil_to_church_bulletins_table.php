<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('church_bulletins', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable()->comment('Uploaded file path (S3/R2)');
            $table->string('thumbnail_url')->nullable()->comment('External image URL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('church_bulletins', function (Blueprint $table) {
            $table->dropColumn('thumbnail_path');
            $table->dropColumn('thumbnail_url');
        });
    }
};
