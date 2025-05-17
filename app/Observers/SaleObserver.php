<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\User;
use Filament\Notifications\Notification;

class SaleObserver
{
    public function updated(Sale $sale): void
    {
        // Si no cambió de estado, abortamos
        if (! $sale->wasChanged('status')) {
            return;
        }

        $oldStatus = $sale->getOriginal('status');
        $newStatus = $sale->status;
        $notes = $sale->tracking_notes ?? null;
        $userId = auth()->id();

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
}
