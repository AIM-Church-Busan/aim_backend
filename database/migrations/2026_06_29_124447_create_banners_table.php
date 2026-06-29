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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('position')->comment('e.g. banner1_main_slide1');
            $table->string('category')->comment('display location group');
            $table->unsignedInteger('order')->default(0);
            $table->string('url')->nullable()->comment('click destination link');
            $table->string('image_url')->comment('S3/R2 image path');
            $table->date('due_date')->nullable()->comment('auto-hide after this date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
