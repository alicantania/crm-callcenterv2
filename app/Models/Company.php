<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

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
        'assigned_operator_id',
        'metadata',
        'contact_person',
    ];

    // Relación con el operador asignado
    public function operator()
    {
        return $this->belongsTo(User::class, 'assigned_operator_id');
    }
    public function calls()
    {
        return $this->hasMany(\App\Models\Call::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

}
