<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Solo agregar si no existe
            if (!Schema::hasColumn('sales', 'status')) {
                $table->enum('status', ['pendiente', 'tramitada', 'incidentada', 'anulada', 'liquidada'])
                    ->default('pendiente')
                    ->after('contract_number');
            }

            if (!Schema::hasColumn('sales', 'tramitated_at')) {
                $table->timestamp('tramitated_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('sales', 'tramitator_id')) {
                $table->foreignId('tramitator_id')->nullable()->constrained('users')->after('tramitated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('sales', 'tramitated_at')) {
                $table->dropColumn('tramitated_at');
            }

            if (Schema::hasColumn('sales', 'tramitator_id')) {
                $table->dropForeign(['tramitator_id']);
                $table->dropColumn('tramitator_id');
            }
        });
    }
};
