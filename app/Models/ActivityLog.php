<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'model_type',
        'model_id',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Obtener el usuario que realizó la acción
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Registrar una nueva actividad
     */
    public static function log(
        string $action, 
        string $description = null, 
        Model $model = null, 
        array $properties = []
    ): self {
        $user = auth()->user();
        
        return self::create([
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'properties' => $properties,
        ]);
    }
}
