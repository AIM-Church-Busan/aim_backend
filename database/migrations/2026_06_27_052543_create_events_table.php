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
Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            // Date & Time
            $table->date('starts_at')->comment('Event start date (required)');
            $table->date('ends_at')->nullable()->comment('Event end date');
            $table->time('start_time')->nullable()->comment('Event start time');
            $table->time('end_time')->nullable()->comment('Event end time');
            $table->date('due_date')->nullable()->comment('Expiry date — automatically unpublished after this date');

            // Location
            $table->string('location')->nullable()->comment('Venue name');
            $table->string('location_address')->nullable()->comment('Detailed address');

            // Image — use either file upload or external URL
            $table->string('thumbnail_path')->nullable()->comment('Uploaded file path (S3/R2)');
            $table->string('thumbnail_url')->nullable()->comment('External image URL');

            // Registration
            $table->unsignedInteger('capacity')->nullable()->comment('Maximum capacity (null = unlimited)');
            $table->unsignedInteger('remaining_spots')->nullable()->comment('Remaining spots — decremented on each registration');

            // Misc
            $table->string('external_link')->nullable();
            $table->string('google_calendar_event_id')->nullable()->comment('Google Calendar sync ID');
            $table->boolean('is_published')->default(false);

            $table->softDeletes();
            $table->timestamps();

            $table->index('starts_at');
            $table->index('due_date');
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
