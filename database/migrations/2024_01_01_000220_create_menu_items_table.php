<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('label', 100);
            $table->string('route', 100);
            $table->string('icon', 80)->default('heroicon-o-squares-2x2');
            $table->string('group', 50)->default('principal')->comment('Seção da sidebar (ex: Hospital, Controle de Acesso, Sistema)');
            $table->tinyInteger('min_level')->unsigned()->default(1)->comment('Nível mínimo de papel para ver o item (1=admin,2=medico,3=recepcionista,4=financeiro)');
            $table->boolean('is_visible')->default(true)->index();
            $table->smallInteger('order')->unsigned()->default(0)->comment('Ordenação dentro do grupo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
