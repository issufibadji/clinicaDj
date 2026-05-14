<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impersonation_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('target_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('end_reason', 50)->nullable();
            $table->string('admin_ip', 45);
            $table->text('admin_user_agent')->nullable();
            $table->unsignedInteger('actions_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('admin_id', 'idx_admin_id');
            $table->index('target_id', 'idx_target_id');
            $table->index('started_at', 'idx_started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impersonation_logs');
    }
};
