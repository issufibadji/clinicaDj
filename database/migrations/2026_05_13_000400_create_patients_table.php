<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('cpf', 14)->unique();
            $table->date('birth_date');
            $table->string('phone', 20);
            $table->string('email', 150)->nullable();
            $table->json('address')->nullable();
            $table->foreignUuid('insurance_id')->nullable()->constrained('insurances')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
