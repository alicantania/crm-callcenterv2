<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',               // Operador que hizo la llamada
        'company_id',
        'status',                // Resultado: interesado, no contesta, etc.
        'duration',
        'call_date',
        'call_time',             // Hora de la llamada
        'notes',
        'contact_person',        // Persona que atendió la llamada
        'motivo_desinteres',     // Motivo si no le interesa
        'recall_at',             // Fecha para volver a llamar
    ];
    
    protected $casts = [
        'call_date' => 'date',
        'call_time' => 'datetime:H:i',
        'recall_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
