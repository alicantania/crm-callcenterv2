<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\Product;
use App\Enums\CompanyStatus;

class CompanySeeder extends Seeder
{
    /**
     * Ejecuta el seeder para crear empresas de prueba.
     */
    public function run(): void
    {
        // Crear 500 empresas completamente libres de operador
        Company::factory(500)->create()->each(function ($empresa) {
            $empresa->updateQuietly([
                'assigned_operator_id' => null,
                'status' => 'nueva',
                'locked_to_operator' => false,
            ]);
        });
    }
    
    /**
     * Obtiene una nota de seguimiento aleatoria.
     */
    private function getRandomFollowUpNote(): string
    {
        $notas = [
            'Cliente interesado en recibir más información por email.',
            'Solicita llamada en horario de tarde.',
            'Contactar después de la reunión de directiva.',
            'Interesado en formación para nuevos empleados.',
            'Pendiente de aprobación de presupuesto.',
            'Quiere comparar con otras ofertas del mercado.',
            'Solicita información sobre bonificaciones.',
            'Interesado pero no es el momento adecuado.',
            'Esperando respuesta del departamento de RRHH.',
            'Necesita consultar con su gestoría.',
        ];
        
        return $notas[array_rand($notas)];
    }
    
    /**
     * Obtiene una modalidad aleatoria para el curso.
     */
    private function getRandomModalidad(): string
    {
        $modalidades = ['online', 'presencial', 'mixta'];
        return $modalidades[array_rand($modalidades)];
    }
    
    /**
     * Obtiene una observación aleatoria sobre el interés en cursos.
     */
    private function getRandomObservacionInteres(): string
    {
        $observaciones = [
            'Interesado principalmente en horario flexible.',
            'Necesita formación para 5-10 empleados.',
            'Prefiere formación intensiva en fin de semana.',
            'Solicita posibilidad de personalizar contenidos.',
            'Interesado en bonificación a través de FUNDAE.',
            'Quiere saber si hay descuentos por volumen.',
            'Pregunta por certificaciones oficiales.',
            'Le interesa especialmente el módulo práctico.',
            'Necesita que la formación sea en sus instalaciones.',
            'Consulta sobre material didáctico incluido.',
        ];
        
        return $observaciones[array_rand($observaciones)];
    }
}
