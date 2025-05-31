<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para crear productos de prueba.
     */
    public function run(): void
    {
        // Cursos de formación bonificada (business_line_id = 1)
        $this->seedFormacionBonificada();

        // Implantaciones privadas (business_line_id = 2)
        $this->seedImplantacionesPrivadas();
    }

    /**
     * Crea productos de formación bonificada (business_line_id = 1).
     */
    private function seedFormacionBonificada(): void
    {
        // Cursos de formación bonificada
        $cursos = [
            // Prevención de riesgos y seguridad
            [
                'name' => 'Curso de Prevención de Riesgos Laborales - Básico',
                'description' => 'Formación bonificada de 30 horas que proporciona los conocimientos básicos sobre prevención de riesgos laborales, cumpliendo con la normativa vigente y capacitando al trabajador para desarrollar su actividad con seguridad.',
                'price' => 300,
            ],
            [
                'name' => 'Curso de Prevención de Riesgos Laborales - Avanzado',
                'description' => 'Formación bonificada de 60 horas que profundiza en la prevención de riesgos laborales con casos prácticos, evaluación de riesgos específicos y desarrollo de planes preventivos adaptados a cada sector.',
                'price' => 500,
            ],
            [
                'name' => 'Curso de Manipulación de Alimentos',
                'description' => 'Formación bonificada de 10 horas que certifica a los trabajadores para manipular alimentos con seguridad, cumpliendo con la normativa sanitaria y garantizando la higiene alimentaria en todos los procesos.',
                'price' => 150,
            ],
            [
                'name' => 'Curso de Primeros Auxilios',
                'description' => 'Formación bonificada de 20 horas que capacita para actuar correctamente ante emergencias médicas en el entorno laboral, incluyendo prácticas de reanimación cardiopulmonar y tratamiento de heridas.',
                'price' => 200,
            ],
            [
                'name' => 'Curso de Seguridad en Maquinaria Industrial',
                'description' => 'Formación bonificada de 25 horas especializada en la prevención de riesgos asociados al uso de maquinaria industrial, incluyendo normativa, dispositivos de seguridad y procedimientos de trabajo seguros.',
                'price' => 350,
            ],
            
            // Ofimática y tecnología
            [
                'name' => 'Curso de Excel Básico',
                'description' => 'Formación bonificada de 20 horas para aprender los fundamentos de Excel, incluyendo creación de hojas de cálculo, fórmulas básicas y formato de celdas para mejorar la productividad administrativa.',
                'price' => 180,
            ],
            [
                'name' => 'Curso de Excel Avanzado',
                'description' => 'Formación bonificada de 30 horas para dominar funciones avanzadas de Excel como tablas dinámicas, macros, funciones complejas y análisis de datos para la toma de decisiones empresariales.',
                'price' => 250,
            ],
            [
                'name' => 'Curso de Ofimática Completo',
                'description' => 'Formación bonificada de 60 horas que abarca el paquete Office completo (Word, Excel, PowerPoint, Outlook) proporcionando habilidades integrales para el trabajo administrativo y la gestión documental.',
                'price' => 280,
            ],
            [
                'name' => 'Curso de PowerBI',
                'description' => 'Formación bonificada de 25 horas para aprender a crear dashboards interactivos y análisis de datos empresariales utilizando Microsoft Power BI, mejorando la visualización y comprensión de datos corporativos.',
                'price' => 350,
            ],
            [
                'name' => 'Curso de Ciberseguridad Básica',
                'description' => 'Formación bonificada de 15 horas sobre los fundamentos de la seguridad informática, incluyendo protección de datos, identificación de amenazas y buenas prácticas para prevenir ciberataques en la empresa.',
                'price' => 220,
            ],
            
            // Idiomas
            [
                'name' => 'Curso de Inglés Empresarial A1-A2',
                'description' => 'Formación bonificada de 60 horas de inglés básico orientado al entorno profesional, desarrollando vocabulario comercial y habilidades comunicativas esenciales para contextos laborales internacionales.',
                'price' => 300,
            ],
            [
                'name' => 'Curso de Inglés Empresarial B1-B2',
                'description' => 'Formación bonificada de 80 horas de inglés intermedio enfocado en negociaciones, presentaciones y comunicación efectiva en entornos corporativos internacionales.',
                'price' => 400,
            ],
            [
                'name' => 'Curso de Francés Comercial',
                'description' => 'Formación bonificada de 60 horas de francés orientado a las relaciones comerciales, incluyendo vocabulario específico, redacción de correos y conversaciones telefónicas en contextos de negocios.',
                'price' => 350,
            ],
            [
                'name' => 'Curso de Alemán para Negocios',
                'description' => 'Formación bonificada de 60 horas que proporciona las bases del idioma alemán en contextos empresariales, facilitando la comunicación con socios y clientes de países germánicos.',
                'price' => 380,
            ],
            
            // Habilidades directivas y soft skills
            [
                'name' => 'Curso de Liderazgo y Gestión de Equipos',
                'description' => 'Formación bonificada de 40 horas para desarrollar competencias directivas, incluyendo técnicas de motivación, delegación efectiva y creación de equipos de alto rendimiento.',
                'price' => 350,
            ],
            [
                'name' => 'Curso de Gestión del Tiempo',
                'description' => 'Formación bonificada de 15 horas para aprender metodologías de planificación, priorización de tareas y herramientas para optimizar el tiempo laboral y aumentar la productividad.',
                'price' => 180,
            ],
            [
                'name' => 'Curso de Comunicación Efectiva',
                'description' => 'Formación bonificada de 20 horas que desarrolla habilidades comunicativas profesionales, incluyendo oratoria, persuasión, lenguaje corporal y técnicas de presentación impactantes.',
                'price' => 240,
            ],
            [
                'name' => 'Curso de Inteligencia Emocional',
                'description' => 'Formación bonificada de 25 horas para desarrollar la capacidad de reconocer y gestionar emociones propias y ajenas, mejorando las relaciones interpersonales y el clima laboral.',
                'price' => 280,
            ],
            [
                'name' => 'Curso de Gestión del Estrés Laboral',
                'description' => 'Formación bonificada de 20 horas que proporciona herramientas prácticas para identificar, prevenir y manejar situaciones de estrés en el entorno laboral, mejorando el bienestar y la productividad.',
                'price' => 220,
            ],
            
            // Ventas y marketing
            [
                'name' => 'Curso de Técnicas de Venta',
                'description' => 'Formación bonificada de 30 horas sobre metodologías de venta consultiva, manejo de objeciones, técnicas de cierre y fidelización de clientes para incrementar resultados comerciales.',
                'price' => 300,
            ],
            [
                'name' => 'Curso de Atención al Cliente',
                'description' => 'Formación bonificada de 20 horas centrada en protocolos de atención, gestión de reclamaciones y técnicas para mejorar la experiencia del cliente y su satisfacción.',
                'price' => 220,
            ],
            [
                'name' => 'Curso de Marketing Digital',
                'description' => 'Formación bonificada de 40 horas sobre estrategias de marketing online, incluyendo SEO, SEM, redes sociales, email marketing y análisis de métricas digitales.',
                'price' => 320,
            ],
            [
                'name' => 'Curso de Negociación Comercial',
                'description' => 'Formación bonificada de 25 horas en técnicas avanzadas de negociación, estrategias de persuasión y toma de decisiones para optimizar acuerdos comerciales y relaciones con proveedores.',
                'price' => 320,
            ],
            
            // Gestión empresarial
            [
                'name' => 'Curso de Gestión de Proyectos',
                'description' => 'Formación bonificada de 40 horas sobre metodologías de gestión de proyectos, incluyendo planificación, asignación de recursos, seguimiento y evaluación de resultados.',
                'price' => 350,
            ],
            [
                'name' => 'Curso de Contabilidad Básica',
                'description' => 'Formación bonificada de 30 horas que proporciona conocimientos fundamentales de contabilidad, incluyendo principios contables, registro de operaciones y elaboración de estados financieros básicos.',
                'price' => 300,
            ],
            [
                'name' => 'Curso de Finanzas para No Financieros',
                'description' => 'Formación bonificada de 25 horas que facilita la comprensión de conceptos financieros clave para directivos y profesionales sin formación específica en finanzas.',
                'price' => 280,
            ],
            [
                'name' => 'Curso de Gestión de Almacenes y Logística',
                'description' => 'Formación bonificada de 30 horas sobre optimización de procesos logísticos, gestión de inventarios, sistemas de almacenamiento y distribución eficiente.',
                'price' => 320,
            ],
            
            // Normativa y compliance
            [
                'name' => 'Curso de Protección de Datos (RGPD)',
                'description' => 'Formación bonificada de 20 horas sobre la normativa de protección de datos, incluyendo obligaciones legales, derechos de los interesados y medidas de seguridad para el cumplimiento del RGPD.',
                'price' => 260,
            ],
            [
                'name' => 'Curso de Compliance Normativo',
                'description' => 'Formación bonificada de 30 horas sobre sistemas de cumplimiento normativo empresarial, prevención de riesgos legales y protocolos de actuación ante posibles infracciones.',
                'price' => 340,
            ],
            [
                'name' => 'Curso de Igualdad de Género en la Empresa',
                'description' => 'Formación bonificada de 20 horas sobre normativa de igualdad, elaboración de planes de igualdad y prevención del acoso laboral en cumplimiento de la legislación vigente.',
                'price' => 240,
            ],
            [
                'name' => 'Curso de Responsabilidad Social Corporativa',
                'description' => 'Formación bonificada de 25 horas sobre implementación de estrategias de RSC, sostenibilidad empresarial y creación de valor compartido con todos los grupos de interés.',
                'price' => 280,
            ],
        ];

        foreach ($cursos as $curso) {
            Product::create([
                'name' => $curso['name'],
                'description' => $curso['description'],
                'price' => $curso['price'],
                'commission_percentage' => rand(10, 20), // comisión random 10%-20%
                'available' => true,
                'business_line_id' => 1, // formación bonificada
            ]);
        }
    }
    
    /**
     * Crea productos de implantaciones privadas (business_line_id = 2).
     */
    private function seedImplantacionesPrivadas(): void
    {
        $implantaciones = [
            // Sistemas de Gestión de Calidad
            [
                'name' => 'Implantación ISO 9001',
                'description' => 'Servicio completo de implantación del Sistema de Gestión de Calidad ISO 9001, incluyendo diagnóstico inicial, documentación, formación del personal, auditoría interna y acompañamiento en la certificación.',
                'price' => 2800,
            ],
            [
                'name' => 'Mantenimiento ISO 9001',
                'description' => 'Servicio anual de mantenimiento del Sistema de Gestión de Calidad ISO 9001, incluyendo actualización de documentación, auditorías internas periódicas y asesoramiento continuo.',
                'price' => 1200,
            ],
            [
                'name' => 'Auditoría Interna ISO 9001',
                'description' => 'Realización de auditoría interna completa del Sistema de Gestión de Calidad según requisitos de la norma ISO 9001, con informe detallado de hallazgos y recomendaciones.',
                'price' => 900,
            ],
            
            // Sistemas de Gestión Ambiental
            [
                'name' => 'Implantación ISO 14001',
                'description' => 'Servicio integral para implementar el Sistema de Gestión Ambiental ISO 14001, incluyendo evaluación ambiental inicial, elaboración de procedimientos, formación y seguimiento hasta la certificación.',
                'price' => 3200,
            ],
            [
                'name' => 'Cálculo de Huella de Carbono',
                'description' => 'Estudio y cálculo de la huella de carbono corporativa según estándares internacionales, con informe detallado y plan de reducción de emisiones personalizado.',
                'price' => 1500,
            ],
            [
                'name' => 'Plan de Gestión de Residuos',
                'description' => 'Elaboración de un plan integral de gestión de residuos adaptado a la normativa vigente, incluyendo caracterización, procedimientos de segregación y seguimiento.',
                'price' => 1800,
            ],
            
            // Seguridad Alimentaria
            [
                'name' => 'Implantación Sistema APPCC',
                'description' => 'Desarrollo e implantación del sistema de Análisis de Peligros y Puntos de Control Crítico para empresas alimentarias, garantizando la seguridad alimentaria y el cumplimiento normativo.',
                'price' => 2400,
            ],
            [
                'name' => 'Certificación IFS Food',
                'description' => 'Servicio completo de preparación para la certificación International Featured Standard Food, incluyendo diagnóstico inicial, adecuación de instalaciones, documentación y formación.',
                'price' => 3500,
            ],
            [
                'name' => 'Certificación BRC Food',
                'description' => 'Implementación del estándar global BRC para seguridad alimentaria, con acompañamiento completo desde la fase inicial hasta la certificación final.',
                'price' => 3800,
            ],
            
            // Protección de Datos
            [
                'name' => 'Adaptación RGPD',
                'description' => 'Servicio completo de adaptación a la normativa de Protección de Datos (RGPD), incluyendo auditoría de cumplimiento, registro de actividades, políticas de privacidad y formación al personal.',
                'price' => 1800,
            ],
            [
                'name' => 'DPO Externo',
                'description' => 'Servicio de Delegado de Protección de Datos externalizado, cumpliendo con los requisitos del RGPD para organizaciones que procesan datos personales a gran escala.',
                'price' => 2400,
            ],
            [
                'name' => 'Evaluación de Impacto en Protección de Datos',
                'description' => 'Realización de EIPD para identificar y minimizar riesgos de privacidad en tratamientos de datos que puedan suponer un alto riesgo para los derechos y libertades.',
                'price' => 1200,
            ],
            
            // Seguridad y Salud Laboral
            [
                'name' => 'Implantación ISO 45001',
                'description' => 'Servicio de implantación del Sistema de Gestión de Seguridad y Salud en el Trabajo, incluyendo evaluación de riesgos, procedimientos, formación y preparación para la certificación.',
                'price' => 3000,
            ],
            [
                'name' => 'Plan de Prevención de Riesgos Laborales',
                'description' => 'Elaboración del Plan de Prevención de Riesgos Laborales completo, adaptado a la actividad específica de la empresa y cumpliendo con la legislación vigente.',
                'price' => 1600,
            ],
            [
                'name' => 'Plan de Emergencia y Evacuación',
                'description' => 'Desarrollo e implantación de Plan de Emergencia y Evacuación personalizado, incluyendo planos, señalización, procedimientos y simulacros prácticos.',
                'price' => 1200,
            ],
            
            // Otros sistemas y certificaciones
            [
                'name' => 'Implantación ISO 27001',
                'description' => 'Implementación del Sistema de Gestión de Seguridad de la Información según ISO 27001, incluyendo análisis de riesgos, controles de seguridad y preparación para la certificación.',
                'price' => 3500,
            ],
            [
                'name' => 'Certificación Esquema Nacional de Seguridad',
                'description' => 'Servicio de adaptación y certificación al Esquema Nacional de Seguridad (ENS) para entidades públicas o que prestan servicios al sector público.',
                'price' => 2800,
            ],
            [
                'name' => 'Sistema Integrado de Gestión',
                'description' => 'Implementación de sistema integrado que combina varias normas (ISO 9001, ISO 14001, ISO 45001) en un único sistema de gestión, optimizando recursos y simplificando procesos.',
                'price' => 4500,
            ],
            [
                'name' => 'Certificación Empresa Familiarmente Responsable',
                'description' => 'Implantación y certificación del modelo EFR para la conciliación de la vida laboral y familiar, mejorando el clima laboral y la reputación corporativa.',
                'price' => 2200,
            ],
            [
                'name' => 'Implantación Compliance Penal',
                'description' => 'Desarrollo e implementación de un sistema de Compliance Penal según UNE 19601, para prevenir delitos y eximir o atenuar la responsabilidad penal de la persona jurídica.',
                'price' => 3200,
            ],
        ];
        
        foreach ($implantaciones as $implantacion) {
            Product::create([
                'name' => $implantacion['name'],
                'description' => $implantacion['description'],
                'price' => $implantacion['price'],
                'commission_percentage' => rand(15, 25),
                'available' => true,
                'business_line_id' => 2, // implantaciones privadas
            ]);
        }
    }
}
