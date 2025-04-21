<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // operador que hizo la llamada
        'company_id',
        'status', // interesado, no contesta, volver a llamar, etc.
        'duration',
        'call_date',
        'call_time',
        'notes',
        'contact_name',
        'contact_role',
        'reschedule_date',
        'reschedule_time',
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
