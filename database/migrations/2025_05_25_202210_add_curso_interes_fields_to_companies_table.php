<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('curso_interesado')->nullable();
            $table->string('modalidad_interesada')->nullable();
            $table->date('fecha_interes')->nullable();
            $table->text('observaciones_interes')->nullable();
            
            // Añadir la clave foránea para curso_interesado
            $table->foreign('curso_interesado')
                  ->references('id')
                  ->on('products')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Eliminar la clave foránea primero
            $table->dropForeign(['curso_interesado']);
            
            // Luego eliminar las columnas
            $table->dropColumn([
                'curso_interesado',
                'modalidad_interesada', 
                'fecha_interes',
                'observaciones_interes'
            ]);
        });
    }
};
