<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        // Datos de empresa
        'company_id',                // FK a companies
        'company_name',
        'cif',
        'address',
        'city',
        'province',
        'phone',
        'email',
        'activity',
        'cnae',
        'contact_person',
        'iban',                      // company_iban en tu tabla
        'social_security',           // ss_company en tu tabla

        // Datos de gestoría
        'gestoria_cif',
        'gestoria_phone',
        'gestoria_email',

        // Datos contacto legal/alumno
        'legal_representative_name',
        'legal_representative_dni',
        'legal_representative_phone',// nombre real en tu tabla
        'legal_representative_ss',   // si lo tienes en la tabla
        'student_name',
        'student_dni',
        'student_phone',
        'student_email',
        'student_social_security',   // ss_student en la tabla

        // Detalles de la venta
        'product_id',
        'business_line_id',
        'operator_id',
        'sale_date',
        'processing_date',           // tramitated_at o processing_date
        'tramitator_id',
        'contract_number',
        'commission_amount',
        'commission_paid_date',
        'liquidated_by',
        'liquidation_date',
        'status',
        'notes',

        // Locking / tracking
        'locked_by_user_id',
    ];

    // Relaciones
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function businessLine()
    {
        return $this->belongsTo(BusinessLine::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function tramitator()
    {
        return $this->belongsTo(User::class, 'tramitator_id');
    }

    public function liquidator()
    {
        return $this->belongsTo(User::class, 'liquidated_by');
    }
}
