<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('company_name');
            }

            if (! Schema::hasColumn('sales', 'iban')) {
                $table->string('iban')->nullable()->after('company_mobile');
            }
            if (! Schema::hasColumn('sales', 'social_security')) {
                $table->string('social_security')->nullable()->after('iban');
            }

            if (! Schema::hasColumn('sales', 'representative_phone')) {
                $table->string('representative_phone')->nullable()->after('legal_representative_dni');
            }

            if (! Schema::hasColumn('sales', 'gestoria_name')) {
                $table->string('gestoria_name')->nullable()->after('legal_representative_phone');
            }
            if (! Schema::hasColumn('sales', 'gestoria_cif')) {
                $table->string('gestoria_cif')->nullable()->after('gestoria_name');
            }
            if (! Schema::hasColumn('sales', 'gestoria_phone')) {
                $table->string('gestoria_phone')->nullable()->after('gestoria_cif');
            }
            if (! Schema::hasColumn('sales', 'gestoria_email')) {
                $table->string('gestoria_email')->nullable()->after('gestoria_phone');
            }

            if (! Schema::hasColumn('sales', 'student_social_security')) {
                $table->string('student_social_security')->nullable()->after('student_dni');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $cols = [
                'contact_person',
                'iban',
                'social_security',
                'representative_phone',
                'gestoria_name',
                'gestoria_cif',
                'gestoria_phone',
                'gestoria_email',
                'student_social_security',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('sales', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
