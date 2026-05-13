<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->char('user_id', 36)->index();
            $table->text('endpoint');
            $table->string('p256dh_key')->nullable();
            $table->string('auth_key')->nullable();
            $table->string('content_encoding')->default('aes128gcm');
            $table->timestamps();

            $table->unique('endpoint');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
