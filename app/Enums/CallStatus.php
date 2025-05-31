<?php

namespace App\Enums;

enum CallStatus: string
{
    case NoContesta = 'no_contesta';
    case NoInteresa = 'no_interesa';
    case VolverALlamar = 'volver_a_llamar';
    case Contacto = 'contacto';
    case Error = 'error';
    case EmailEnviado = 'email_enviado';
    case VentaRealizada = 'venta_realizada';
    
    public function label(): string
    {
        return match($this) {
            self::NoContesta => 'No contesta',
            self::NoInteresa => 'No interesa',
            self::VolverALlamar => 'Volver a llamar',
            self::Contacto => 'Contacto realizado',
            self::Error => 'Error/Teléfono inválido',
            self::EmailEnviado => 'Email enviado',
            self::VentaRealizada => 'Venta realizada',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::NoContesta => 'yellow',
            self::NoInteresa => 'red',
            self::VolverALlamar => 'blue',
            self::Contacto => 'green',
            self::Error => 'gray',
            self::EmailEnviado => 'purple',
            self::VentaRealizada => 'success',
        };
    }
}
