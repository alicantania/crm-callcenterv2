<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'last_name',
        'middle_name',
        'email',
        'email_verified_at',
        'phone',
        'mobile',
        'address',
        'birth_date',
        'identification_number',
        'password',
        'extension',
        'contract_start_date',
        'contract_end_date',
        'contract_hours',
        'commission_rate',
        'personal_commissions',
        'remember_token',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'     => 'datetime',
        'birth_date'            => 'date',
        'contract_start_date'   => 'date',
        'contract_end_date'     => 'date',
        'commission_rate'       => 'float',
        'personal_commissions'  => 'float',
    ];

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $segment) => Str::of($segment)->substr(0, 1))
            ->implode('');
    }

    public function businessLines()
    {
        return $this->belongsToMany(BusinessLine::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function companies()
    {
        return $this->hasMany(Company::class, 'assigned_operator_id');
    }

    public function hasRole(array|string $roles): bool
    {
        return in_array($this->role->name, (array) $roles);
    }

    // Relación para asociar llamadas
    public function calls()
    {
        return $this->hasMany(Call::class, 'user_id');
    }

    // Relación para asociar ventas del operador
    public function sales()
    {
        return $this->hasMany(Sale::class, 'operator_id');
    }


}
