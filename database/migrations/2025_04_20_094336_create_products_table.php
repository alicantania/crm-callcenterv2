<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2); // Precio que paga el cliente
            $table->enum('commission_type', ['fijo', 'porcentaje'])->default('porcentaje'); // Tipo de comisión para el operador
            $table->decimal('commission_value', 8, 2)->default(0); // Valor fijo o porcentaje de comisión
            $table->boolean('available')->default(true);
            $table->foreignId('business_line_id')->constrained()->onDelete('cascade'); // Bonificada o implantación privada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
