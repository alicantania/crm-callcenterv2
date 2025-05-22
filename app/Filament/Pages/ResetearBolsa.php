<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use Filament\Notifications\Notification;
use App\Notifications\EmpresasLiberadasNotification;
use App\Helpers\RoleHelper;

class ResetearBolsa extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static string $view = 'filament.pages.resetear-bolsa';
    protected static ?string $navigationLabel = 'Resetear Bolsa de Llamadas';
    protected static ?string $navigationGroup = 'Gerencia';
    protected static ?int $navigationSort = 50;

    public static function shouldRegisterNavigation(): bool
    {
        // Solo mostrar a todos menos 'Operador'
        return RoleHelper::userHasNotRole(['Operador']);
    }

    public function resetear()
    {
        $usuario = Auth::user();

        $liberadas = Company::whereNotNull('assigned_operator_id')
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('sales')
                    ->whereColumn('sales.company_id', 'companies.id');
            })
            ->whereHas('calls', function ($query) {
                $query->whereDate('call_date', '<', now()->subMonth());
            })
            ->update(['assigned_operator_id' => null]);

        // Toast (voladora)
        Notification::make()
            ->title("✅ {$liberadas} empresas liberadas")
            ->body("Acción realizada por {$usuario->name}")
            ->success()
            ->send();

        
        // Notificación persistente (campanita)
        Notification::make()
            ->title('🧹 Empresas liberadas')
            ->body("{$liberadas} empresas fueron reseteadas correctamente.")
            ->success()
            ->persistent() // <- esto evita que se borre sola
            // Filament ya añade el campo 'format' automáticamente
            ->sendToDatabase($usuario);
    }
}
