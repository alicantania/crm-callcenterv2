<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // Datos completos de la empresa en el momento de la venta
            $table->string('company_name');
            $table->string('cif');
            $table->string('address');
            $table->string('city');
            $table->string('province');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('activity')->nullable();
            $table->string('cnae')->nullable();

            // Operador que realizó la venta
            $table->foreignId('operator_id')->constrained('users');
            $table->timestamp('sale_date');

            // Producto vendido y su línea de negocio
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('business_line_id')->constrained('business_lines');

            // Tramitador (administrador) y fecha de tramitación
            $table->foreignId('tramitator_id')->nullable()->constrained('users');
            $table->timestamp('processing_date')->nullable();
            $table->string('contract_number')->nullable();

            // Comisión obtenida por el operador
            $table->decimal('commission_amount', 10, 2)->nullable();
            $table->timestamp('commission_paid_date')->nullable();

            // Usuario que liquida la venta (gerencia)
            $table->foreignId('liquidated_by')->nullable()->constrained('users');
            $table->timestamp('liquidation_date')->nullable();

            // Datos adicionales de la venta
            $table->string('legal_representative')->nullable();
            $table->string('legal_representative_dni')->nullable();
            $table->string('legal_representative_phone')->nullable();
            $table->string('gestoria_cif')->nullable();
            $table->string('gestoria_phone')->nullable();
            $table->string('gestoria_email')->nullable();
            $table->string('student_dni')->nullable();
            $table->string('student_phone')->nullable();
            $table->string('student_email')->nullable();
            $table->string('company_iban')->nullable();
            $table->string('ss_company')->nullable();
            $table->string('ss_student')->nullable();

            // Estado de la venta
            $table->string('status')->default('pending'); // 'pending', 'in_process', 'processed'

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
