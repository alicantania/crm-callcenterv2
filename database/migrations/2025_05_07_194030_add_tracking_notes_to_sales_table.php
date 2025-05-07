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
        Schema::table('sales', function (Blueprint $table) {
            $table->text('tracking_notes')->nullable()->after('tramitator_id');
        });
    }
    
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('tracking_notes');
        });
    }
    
};
