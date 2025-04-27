<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'product_id',
        'business_line_id',
        'operator_id',
        'sale_date',
        'tramitator_id',
        'tramitated_at',
        'contract_number',
        'commission',
        'liquidator_id',
        'liquidated_at',
        // Nuevos campos para representar legal y alumno
        'legal_representative_name',
        'legal_representative_dni',
        'legal_representative_ss',
        'student_name',
        'student_dni',
        'student_ss',
        'student_email',
        'student_phone',
        'notes',
    ];

    // Relación con la empresa
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Relación con el producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relación con la línea de negocio
    public function businessLine()
    {
        return $this->belongsTo(BusinessLine::class);
    }

    // Operador que hizo la venta (rol operador)
    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    // Tramitador de la venta (rol administración)
    public function tramitator()
    {
        return $this->belongsTo(User::class, 'tramitator_id');
    }

    // Liquidación por parte de gerencia (rol gerencia)
    public function liquidator()
    {
        return $this->belongsTo(User::class, 'liquidator_id');
    }
   
}
