<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class EmpresasLiberadasNotification extends Notification
{
    public function __construct(public int $liberadas) {}

    public function via($notifiable): array
    {
        return ['database']; // ✅ Filament necesita que sea vía base de datos
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => '🧹 Empresas liberadas',
            'body' => "{$this->liberadas} empresas fueron reseteadas correctamente.",
            'format' => 'filament', 
            'duration' => null, // ✅ Requisito para que se vea en la campana
        ];
    }
}
