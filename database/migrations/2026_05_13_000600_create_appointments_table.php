<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('room_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('scheduled_at')->index();
            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled'])
                ->default('scheduled')
                ->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
