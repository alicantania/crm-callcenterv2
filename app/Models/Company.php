<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'cif',
        'name',
        'address',
        'city',
        'province',
        'phone',
        'email',
        'activity',
        'cnae',
        'assigned_operator_id', // <- Este es el nombre correcto según tu base de datos
    ];

    // Relación con el operador asignado
    public function operator()
    {
        return $this->belongsTo(User::class, 'assigned_operator_id');
    }
}
