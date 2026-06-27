<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    Schema::create('announcements', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();

                // Date & Time
                $table->date('starts_at')->comment('Start date (required)');
                $table->date('ends_at')->nullable()->comment('End date');
                $table->time('start_time')->nullable()->comment('Start time');
                $table->time('end_time')->nullable()->comment('End time');
                $table->date('due_date')->nullable()->comment('Expiry date — automatically unpublished after this date');

                // Location
                $table->string('location')->nullable()->comment('Venue name');
                $table->string('location_address')->nullable()->comment('Detailed address');

                // Image — use either file upload or external URL
                $table->string('thumbnail_path')->nullable()->comment('Uploaded file path (S3/R2)');
                $table->string('thumbnail_url')->nullable()->comment('External image URL');

                // Category
                $table->enum('category', ['general', 'children', 'offering'])->default('general');

                // Publishing
                $table->boolean('is_pinned')->default(false);
                $table->boolean('is_published')->default(false);
                $table->timestamp('published_at')->nullable()->comment('Scheduled publish time');

                $table->timestamps();

                $table->index('category');
                $table->index('is_pinned');
                $table->index('is_published');
                $table->index('due_date');
            });
};
