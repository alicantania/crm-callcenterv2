<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Formatea todos los números de teléfono para que tengan 9 dígitos y empiecen por 9 o 6.
     */
    public function up(): void
    {
        // Obtener todas las empresas
        $companies = Company::all();
        
        foreach ($companies as $company) {
            // Limpiar el número de teléfono (eliminar espacios, guiones, paréntesis, etc.)
            $phone = preg_replace('/[^0-9]/', '', $company->phone);
            
            // Si está vacío, generar uno nuevo
            if (empty($phone)) {
                $phone = (rand(0, 1) ? '9' : '6') . rand(10000000, 99999999);
            } 
            // Si no comienza con 9 o 6, añadir un prefijo
            elseif (!preg_match('/^[96]/', $phone)) {
                $phone = (rand(0, 1) ? '9' : '6') . substr($phone, 0, 8);
            }
            // Si tiene más de 9 dígitos, truncar
            elseif (strlen($phone) > 9) {
                $phone = substr($phone, 0, 9);
            }
            // Si tiene menos de 9 dígitos, rellenar
            elseif (strlen($phone) < 9) {
                $phone = $phone . str_repeat('0', 9 - strlen($phone));
            }
            
            // Actualizar el teléfono
            $company->phone = $phone;
            $company->save();
        }
        
        // Registrar en log
        DB::table('activity_logs')->insert([
            'user_id' => null,
            'action' => 'system',
            'description' => 'Se han formateado todos los números de teléfono de las empresas',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     * No se puede revertir esta migración ya que es una transformación de datos.
     */
    public function down(): void
    {
        // No es posible revertir esta migración
    }
};
