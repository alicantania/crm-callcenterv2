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
        'contacts_count',
        'last_contact_at',
        'status',
        'locked_to_operator',
        'locked_at',
        'curso_interesado',
        'modalidad_interesada',
        'fecha_interes',
        'observaciones_interes',
        'internal_note',
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
    
    /**
     * Get the email requests for the company.
     */
    public function emailRequests()
    {
        return $this->hasMany(EmailRequest::class);
    }
    
    /**
     * Get the course that the company is interested in.
     */
    public function cursoInteresado()
    {
        return $this->belongsTo(Product::class, 'curso_interesado');
    }
    
    /**
     * Incrementa el contador de contactos y actualiza la fecha del último contacto.
     *
     * @return $this
     */
    public function incrementarContactos()
    {
        $this->increment('contacts_count');
        $this->update(['last_contact_at' => now()]);
        
        return $this;
    }
    
    /**
     * Método auxiliar para desbloquear una empresa
     * 
     * @return $this
     */
    public function unlock()
    {
        $this->update([
            'assigned_operator_id' => null,
            'locked_at' => null,
            'locked_to_operator' => false,
        ]);
        
        return $this;
    }
    
    /**
     * Método para bloquear una empresa para un operador específico
     * 
     * @param int $operatorId
     * @return $this
     */
    public function lockForOperator(int $operatorId)
    {
        $this->update([
            'assigned_operator_id' => $operatorId,
            'locked_at' => now(),
            'locked_to_operator' => true,
        ]);
        
        return $this;
    }
    
    /**
     * Registra una llamada para esta empresa
     *
     * @param string $status El estado de la llamada (usar CallStatus enum)
     * @param string|null $notes Notas adicionales
     * @param int|null $duration Duración en segundos
     * @param string|null $contactPerson Persona de contacto
     * @param \DateTime|string|null $recallAt Fecha para volver a llamar
     * @return \App\Models\Call
     */
    public function registerCall(string $status, ?string $notes = null, ?int $duration = null, ?string $contactPerson = null, $recallAt = null)
    {
        // Incrementar contador de contactos
        $this->incrementarContactos();
        
        // Crear registro de llamada
        $call = Call::create([
            'user_id' => auth()->id(),
            'company_id' => $this->id,
            'call_date' => now(),
            'duration' => $duration ?? rand(60, 300),
            'status' => $status,
            'recall_at' => $recallAt,
            'notes' => $notes,
            'contact_person' => $contactPerson ?? $this->contact_person,
        ]);
        
        // Actualizar estado de la empresa según el resultado de la llamada
        if (in_array($status, ['contacto', 'volver_a_llamar'])) {
            $this->update(['status' => 'contactada']);
        } elseif ($status === 'no_interesa') {
            $this->update(['status' => 'no_interesa']);
        } elseif ($status === 'no_contesta') {
            $this->update(['status' => 'no_contesta']);
        }
        
        return $call;
    }
}
