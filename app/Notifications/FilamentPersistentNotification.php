<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class FilamentPersistentNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return \Filament\Notifications\Notification::make()
            ->title('¡Notificación persistente!')
            ->body('Esto aparece en la campanita de Filament.')
            ->icon('heroicon-o-bell')
            ->color('success')
            ->getDatabaseMessage();
    }
}
