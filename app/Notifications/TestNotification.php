<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TestNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Notificación Laravel pura',
            'body' => 'Si esto se guarda, Laravel está bien',
            'format' => 'filament',         // 👈 CLAVE para que Filament lo muestre
            'duration' => null,             // 👈 CLAVE para que NO se borre sola
        ];
    }
}
