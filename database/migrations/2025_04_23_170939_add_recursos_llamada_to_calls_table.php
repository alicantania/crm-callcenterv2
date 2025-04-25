<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            if (!Schema::hasColumn('calls', 'recall_at')) {
                $table->timestamp('recall_at')->nullable();
            }

            if (!Schema::hasColumn('calls', 'motivo_desinteres')) {
                $table->string('motivo_desinteres')->nullable();
            }

            if (!Schema::hasColumn('calls', 'contact_person')) {
                $table->string('contact_person')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn(['recall_at', 'motivo_desinteres', 'contact_person']);
        });
    }
};

