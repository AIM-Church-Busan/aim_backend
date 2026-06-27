<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('planning_center_user_id')
                ->constrained('planning_center_users')
                ->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            // Prevent duplicate likes from the same user on the same event
            $table->unique(['event_id', 'planning_center_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_likes');
    }
};
