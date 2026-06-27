<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_life_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_center_user_id')
                ->constrained('planning_center_users')
                ->cascadeOnDelete();

            // Life Group Info — fetched from GET /groups/v2/people/{person_id}/memberships
            $table->string('life_group_id')->comment('Planning Center Group ID');
            $table->string('life_group_name')->comment('Life Group name');
            $table->enum('role', ['leader', 'member'])->default('member')->comment('Role in the Life Group');
            $table->timestamp('joined_at')->nullable()->comment('Date joined the Life Group');

            $table->timestamps();

            // Prevent duplicate membership records per user per group
            $table->unique(['planning_center_user_id', 'life_group_id']);

            $table->index('life_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_life_groups');
    }
};
