<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Illuminate\Notifications\Notification as LaravelBaseNotification;
use App\Models\User;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        // Datos de la empresa (coinciden con columnas de BBDD)
        'company_name',
        'cif',
        'address',
        'city',
        'province',
        'phone',
        'email',
        'activity',
        'cnae',

        // Información de contacto (si existen en BBDD):
        'company_iban',
        'ss_company',
        'ss_student',
        
        // Línea de negocio y producto
        'product_id',
        'business_line_id',

        // Datos de la venta
        'sale_price',
        'commission_amount',
        'sale_date',
        'operator_id',

        // Flujo de tramitación
        'tramitator_id',
        'tramitated_at',
        'processing_date',
        'contract_number',
        'commission_paid_date',
        'liquidated_by',
        'liquidation_date',
        'status',

        // Representante legal
        'legal_representative_name',
        'legal_representative_dni',
        'legal_representative_phone',

        // Gestoría
        'gestoria_cif',
        'gestoria_phone',
        'gestoria_email',

        // Alumno
        'student_name',
        'student_dni',
        'student_ss',
        'student_phone',
        'student_email',

        // Relaciones
        'company_id',
        'locked_by_user_id',

        // Campos auxiliares
        'iva_percentage',
        'additional_info',
        'tracking_notes',

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

    protected static function booted()
    {
        static::created(function ($sale) {
            // Buscar al primer usuario con rol Admin
            $admin = User::whereHas('role', fn ($query) => $query->where('name', 'Admin'))->first();

            if (! $admin) {
                return;
            }

            LaravelNotification::send($admin, new class($sale) extends LaravelBaseNotification {
                public function __construct(public $sale) {}

                public function via($notifiable): array
                {
                    return ['database'];
                }

                public function toArray($notifiable): array
                {
                    return [
                        'title' => '📝 Nueva venta pendiente de tramitar',
                        'body' => "La empresa ID {$this->sale->company_id} tiene una venta creada por el operador ID {$this->sale->operator_id}.",
                        'format' => 'filament',
                    ];
                }
            });
        });
    }
}
