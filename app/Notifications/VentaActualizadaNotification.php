<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class VentaActualizadaNotification extends Notification
{
    use Queueable;

    protected string $ventaId;
    protected string $nuevoEstado;
    protected string $empresa;
    protected ?string $mensaje;

    public function __construct(string $ventaId, string $nuevoEstado, string $empresa, ?string $mensaje = null)
    {
        $this->ventaId = $ventaId;
        $this->nuevoEstado = $nuevoEstado;
        $this->empresa = $empresa;
        $this->mensaje = $mensaje;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        // Configurar color e icono según el estado
        $color = match($this->nuevoEstado) {
            'devuelta' => 'danger',
            'tramitada' => 'success',
            'pendiente' => 'warning',
            'anulada' => 'danger',
            'incidentada' => 'danger',
            'liquidada' => 'success',
            default => 'primary',
        };

        $icono = match($this->nuevoEstado) {
            'devuelta' => 'heroicon-o-exclamation-triangle',
            'tramitada' => 'heroicon-o-check-circle',
            'pendiente' => 'heroicon-o-clock',
            'anulada' => 'heroicon-o-x-mark',
            'incidentada' => 'heroicon-o-exclamation-circle',
            default => 'heroicon-o-bell',
        };

        // Emoji para el titulo
        $emoji = match($this->nuevoEstado) {
            'devuelta' => '‼️',
            'tramitada' => '✅',
            'pendiente' => '⏰',
            'anulada' => '❌',
            'incidentada' => '⚠️',
            'seguimiento' => '🔍',
            default => '💬',
        };

        // Crear el mensaje de notificación
        $titulo = "{$emoji} Venta #{$this->ventaId} - {$this->nuevoEstado}";
        $cuerpo = "La venta para {$this->empresa} ha pasado a estado: {$this->nuevoEstado}.";
        
        // Agregar mensaje adicional si existe
        if ($this->mensaje) {
            $cuerpo .= " {$this->mensaje}";
        }

        return FilamentNotification::make()
            ->title($titulo)
            ->body($cuerpo)
            ->icon($icono)
            ->color($color)
            ->persistent() // Garantizamos que SIEMPRE sea persistente
            ->getDatabaseMessage();
    }
}
