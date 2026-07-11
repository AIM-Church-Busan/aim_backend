<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('planning_center_user_id')
                ->constrained('planning_center_users')
                ->cascadeOnDelete();

            $table->string('status')->default('not_registered')->comment('not_registered | registered | cancelled');

            $table->timestamps();

            $table->unique(['event_id', 'planning_center_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
