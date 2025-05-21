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
        Schema::table("companies", function (Blueprint $table) {
            $table->jsonb("metadata")->nullable();
            $table->string("contact_person")->nullable()->after("cnae");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("companies", function (Blueprint $table) {
            $table->dropColumn("metadata");
            $table->dropColumn("contact_person");
        });
    }
};
