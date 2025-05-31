<?php

namespace App\Enums;

enum CompanyStatus: string
{
    case Nueva = 'nueva';
    case Contactada = 'contactada';
    case Seguimiento = 'seguimiento';
    case NoInteresa = 'no_interesa';
    case NoContesta = 'no_contesta';
    case ClienteActivo = 'cliente_activo';
    case ClienteInactivo = 'cliente_inactivo';
    
    public function label(): string
    {
        return match($this) {
            self::Nueva => 'Nueva',
            self::Contactada => 'Contactada',
            self::Seguimiento => 'Seguimiento',
            self::NoInteresa => 'No interesa',
            self::NoContesta => 'No contesta',
            self::ClienteActivo => 'Cliente activo',
            self::ClienteInactivo => 'Cliente inactivo',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::Nueva => 'gray',
            self::Contactada => 'blue',
            self::Seguimiento => 'green',
            self::NoInteresa => 'red',
            self::NoContesta => 'yellow',
            self::ClienteActivo => 'purple',
            self::ClienteInactivo => 'orange',
        };
    }
}
