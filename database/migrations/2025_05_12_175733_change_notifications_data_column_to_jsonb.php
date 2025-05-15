<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Solo ejecutar en PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<SQL
                ALTER TABLE notifications
                ALTER COLUMN data TYPE jsonb USING data::jsonb;
            SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<SQL
                ALTER TABLE notifications
                ALTER COLUMN data TYPE text USING data::text;
            SQL);
        }
    }
};
