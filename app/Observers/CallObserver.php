<?php

namespace App\Observers;

use App\Models\Call;
use App\Services\ActivityLogService;

class CallObserver
{
    /**
     * Handle the Call "created" event.
     */
    public function created(Call $call): void
    {
        ActivityLogService::logCreated(
            $call, 
            "Se ha registrado una nueva llamada a la empresa {$call->company->name} con estado: {$call->status}"
        );
    }

    /**
     * Handle the Call "updated" event.
     */
    public function updated(Call $call): void
    {
        $changes = $call->getChanges();
        
        // Registrar específicamente cambios en status
        if (isset($changes['status'])) {
            $oldStatus = $call->getOriginal('status');
            $newStatus = $changes['status'];
            
            ActivityLogService::log(
                'call_status_changed',
                "Llamada ID: {$call->id} cambió de estado: {$oldStatus} a {$newStatus}",
                $call,
                [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'company_id' => $call->company_id,
                    'company_name' => $call->company->name,
                ]
            );
        }
        
        ActivityLogService::logUpdated($call, $changes, "Se ha actualizado la llamada ID: {$call->id}");
    }

    /**
     * Handle the Call "deleted" event.
     */
    public function deleted(Call $call): void
    {
        ActivityLogService::logDeleted(
            $call, 
            "Se ha eliminado la llamada ID: {$call->id} a la empresa {$call->company->name}"
        );
    }

    /**
     * Handle the Call "restored" event.
     */
    public function restored(Call $call): void
    {
        ActivityLogService::log(
            'restore',
            "Se ha restaurado la llamada ID: {$call->id} a la empresa {$call->company->name}",
            $call
        );
    }

    /**
     * Handle the Call "force deleted" event.
     */
    public function forceDeleted(Call $call): void
    {
        ActivityLogService::log(
            'force_delete',
            "Se ha eliminado permanentemente la llamada ID: {$call->id}",
            $call
        );
    }
}
