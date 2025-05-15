<?php

namespace App\Livewire;

use Filament\Notifications\Livewire\NotificationsComponent as BaseComponent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class CustomNotificationsComponent extends BaseComponent
{
    public function mount(): void
    {
        // ❌ No hacemos nada que marque como leído automáticamente
    }

    public function close(string $id): void
    {
        // ❌ Anulado: no se borra de la base
        // parent::close($id); // NO LLAMAMOS A ESTO
    }

    public function markAllAsRead(): void
    {
        // ❌ No marcamos como leídas por defecto
        // parent::markAllAsRead(); // NO LLAMAMOS A ESTO
    }

    public function getUnreadNotificationsProperty()
    {
        if (!Auth::check()) {
            return collect();
        }

        return DB::table('notifications')
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', Auth::id())
            ->whereNull('read_at')

            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return new DatabaseNotification((array) $notification);
            });
    }
}
