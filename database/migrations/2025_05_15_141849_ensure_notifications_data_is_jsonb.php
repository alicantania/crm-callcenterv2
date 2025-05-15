<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // Primero aseguramos que la columna existe
            if (Schema::hasColumn('notifications', 'data')) {
                // Convertimos a JSONB para PostgreSQL
                DB::statement(<<<SQL
                    ALTER TABLE notifications
                    ALTER COLUMN data TYPE jsonb USING data::jsonb;
                SQL);
            } else {
                // Si no existe, la creamos como JSONB
                Schema::table('notifications', function (Blueprint $table) {
                    $table->jsonb('data');
                });
            }
        } else {
            // Para otros motores (SQLite, MySQL) usamos JSON
            if (Schema::hasColumn('notifications', 'data')) {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->json('data')->change();
                });
            } else {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->json('data');
                });
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<SQL
                ALTER TABLE notifications
                ALTER COLUMN data TYPE text USING data::text;
            SQL);
        } else {
            Schema::table('notifications', function (Blueprint $table) {
                $table->text('data')->change();
            });
        }
    }
};
