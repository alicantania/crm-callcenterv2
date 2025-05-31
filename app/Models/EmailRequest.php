<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailRequest extends Model
{
    use HasFactory;
    
    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'product_id',
        'requested_by_id',
        'processed_by_id',
        'email_to',
        'contact_person',
        'notes',
        'admin_notes',
        'status',
        'processed_at',
    ];
    
    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'processed_at' => 'datetime',
    ];
    
    /**
     * Get the company that owns the email request.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    
    /**
     * Get the product that is associated with the email request.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the user that requested the email.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }
    
    /**
     * Get the admin that processed the email request.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_id');
    }
    
    /**
     * Scope a query to only include pending email requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope a query to only include processed email requests.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
    
    /**
     * Scope a query to only include cancelled email requests.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
    
    /**
     * Mark the email request as processed.
     */
    public function markAsProcessed(int $adminId, ?string $notes = null): self
    {
        $this->update([
            'status' => 'processed',
            'processed_by_id' => $adminId,
            'processed_at' => now(),
            'admin_notes' => $notes,
        ]);
        
        return $this;
    }
    
    /**
     * Mark the email request as cancelled.
     */
    public function markAsCancelled(int $adminId, ?string $notes = null): self
    {
        $this->update([
            'status' => 'cancelled',
            'processed_by_id' => $adminId,
            'processed_at' => now(),
            'admin_notes' => $notes,
        ]);
        
        return $this;
    }
}
