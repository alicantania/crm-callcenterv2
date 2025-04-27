<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('locked_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('operator_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['locked_by_user_id']);
            $table->dropColumn('locked_by_user_id');
        });
    }
};
