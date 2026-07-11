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

            $table->timestamps();

            // 같은 유저가 같은 이벤트에 중복 등록하는 것 방지
            $table->unique(['event_id', 'planning_center_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
