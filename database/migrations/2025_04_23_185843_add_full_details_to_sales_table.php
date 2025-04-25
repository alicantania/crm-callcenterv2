<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->text('legal_representative_name')->nullable();
            $table->string('legal_representative_ss')->nullable();

            $table->text('student_name')->nullable();
            $table->string('student_ss')->nullable();
            
            

            $table->string('gestoria')->nullable();
            $table->string('iva_percentage')->nullable();
            $table->text('additional_info')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'legal_representative_name',
                'legal_representative_dni',
                'legal_representative_ss',
                'student_name',
                'student_dni',
                'student_ss',
                'student_email',
                'student_phone',
                'gestoria',
                'iva_percentage',
                'additional_info',
            ]);
        });
    }
};
