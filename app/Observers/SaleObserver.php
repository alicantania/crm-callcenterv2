<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\User;
use App\Services\ActivityLogService;
use Filament\Notifications\Notification;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        ActivityLogService::logCreated(
            $sale, 
            "Se ha registrado una nueva venta para la empresa {$sale->company_name} con estado: {$sale->status}"
        );
    }
    public function updated(Sale $sale): void
    {
        // Registrar en el sistema de logs
        $changes = $sale->getChanges();
        // Si no cambió de estado, solo registramos la actualización general
        if (! $sale->wasChanged('status')) {
            ActivityLogService::logUpdated($sale, $changes, "Se ha actualizado la venta ID: {$sale->id}");
            return;
        }

        $oldStatus = $sale->getOriginal('status');
        $newStatus = $sale->status;
        $notes = $sale->tracking_notes ?? null;
        $userId = auth()->id();

        // Registrar en el sistema de logs
        ActivityLogService::log(
            'sale_status_changed',
            "Venta ID: {$sale->id} cambió de estado: {$oldStatus} a {$newStatus}",
            $sale,
            [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'company_name' => $sale->company_name,
                'changed_by' => $userId,
                'notes' => $notes
            ]
        );
        
        // Registrar tracking
        \App\Models\SaleTracking::create([
            'sale_id'    => $sale->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes'      => $notes,
            'changed_by' => $userId,
        ]);

        // Notificar SIEMPRE al operador
        $operator = $sale->operator;
        if ($operator) {
            // Notificación persistente (campanita)
            $operator->notify(new \App\Notifications\VentaActualizadaNotification(
                $sale->id,
                $sale->status,
                $sale->company_name
            ));

            // Toast voladora (solo título y estado)
            \Filament\Notifications\Notification::make()
                ->title("Venta #{$sale->id} actualizada")
                ->body("Tu venta ha pasado a estado: {$sale->status}.")
                ->icon('heroicon-o-check')
                ->color('success')
                ->send($operator);
        }

        // Mantener notificaciones a otros roles si aplica
        match ($newStatus) {
            'pendiente' => $this->notifyTramitadores($sale),
            'devuelta'  => $this->notifyOperador($sale),
            'tramitada' => $this->notifyGerencia($sale),
            default     => null,
        };
    }

    protected function notifyTramitadores(Sale $sale): void
    {
        $recipients = User::where('role_id', 2)->get(); // rol 2 = tramitador
        Notification::make()
            ->title("Venta #{$sale->id} en tramitación")
            ->body("La venta de {$sale->company_name} está lista para tramitar.")
            ->success()
            ->sendToDatabase($recipients);
    }

    protected function notifyOperador(Sale $sale): void
    {
        $operator = $sale->operator;
        Notification::make()
            ->title("Venta #{$sale->id} devuelta")
            ->body("Tu venta fue devuelta para correcciones.")
            ->danger()
            ->sendToDatabase($operator);
    }

    protected function notifyGerencia(Sale $sale): void
    {
        $recipients = User::where('role_id', 3)->get(); // rol 3 = gerencia
        Notification::make()
            ->title("Venta #{$sale->id} tramitada")
            ->body("La venta ha sido tramitada correctamente.")
            ->info()
            ->sendToDatabase($recipients);
    }
    
    /**
     * Handle the Sale "deleted" event.
     */
    public function deleted(Sale $sale): void
    {
        ActivityLogService::logDeleted(
            $sale, 
            "Se ha eliminado la venta ID: {$sale->id} de la empresa {$sale->company_name}"
        );
    }

    /**
     * Handle the Sale "restored" event.
     */
    public function restored(Sale $sale): void
    {
        ActivityLogService::log(
            'restore',
            "Se ha restaurado la venta ID: {$sale->id} de la empresa {$sale->company_name}",
            $sale
        );
    }

    /**
     * Handle the Sale "force deleted" event.
     */
    public function forceDeleted(Sale $sale): void
    {
        ActivityLogService::log(
            'force_delete',
            "Se ha eliminado permanentemente la venta ID: {$sale->id}",
            $sale
        );
    }
}
