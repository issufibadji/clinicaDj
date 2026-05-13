<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->index('status', 'payments_status_idx');
            $table->index('created_at', 'payments_created_at_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index('date', 'expenses_date_idx');
            $table->index('category', 'expenses_category_idx');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index('start_at', 'events_start_at_idx');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->index('name', 'patients_name_idx');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->index('status', 'appointments_status_idx');
            $table->index('scheduled_at', 'appointments_scheduled_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_status_idx');
            $table->dropIndex('payments_created_at_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_date_idx');
            $table->dropIndex('expenses_category_idx');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_start_at_idx');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('patients_name_idx');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_status_idx');
            $table->dropIndex('appointments_scheduled_at_idx');
        });
    }
};
