<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Operador que realiza la llamada
            $table->foreignId('company_id')->constrained()->onDelete('cascade'); // Empresa contactada
            $table->timestamp('call_date')->nullable();
            $table->integer('duration')->nullable(); // En segundos
            $table->string('status')->nullable(); // ej: "completada", "no contestó", etc.
            $table->text('notes')->nullable(); // Comentarios adicionales
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
