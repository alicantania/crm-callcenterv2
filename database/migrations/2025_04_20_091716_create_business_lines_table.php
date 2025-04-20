<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_lines', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nombre de la línea de negocio');
            $table->text('description')->nullable()->comment('Descripción de la línea de negocio');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_lines');
    }
};
