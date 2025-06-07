<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLogService;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        ActivityLogService::logCreated($user, "Se ha creado un nuevo usuario: {$user->name} {$user->last_name}");
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        
        // No registrar actualizaciones de last_login_at
        if (count($changes) === 2 && isset($changes['updated_at']) && isset($changes['last_login_at'])) {
            return;
        }
        
        ActivityLogService::logUpdated($user, $changes, "Se ha actualizado el usuario: {$user->name} {$user->last_name}");
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        ActivityLogService::logDeleted($user, "Se ha eliminado el usuario: {$user->name} {$user->last_name}");
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        ActivityLogService::log(
            'restore',
            "Se ha restaurado el usuario: {$user->name} {$user->last_name}",
            $user
        );
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        ActivityLogService::log(
            'force_delete',
            "Se ha eliminado permanentemente el usuario: {$user->name} {$user->last_name}",
            $user
        );
    }
}
