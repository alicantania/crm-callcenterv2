<?php

namespace App\Filament\Pages;

use App\Models\Company;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ResetearBolsa extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trash';

    protected static string $view = 'filament.pages.resetear-bolsa';

    protected static ?string $navigationLabel = '🧹 Resetear Bolsa de Llamadas';

    protected static ?int $navigationSort = 100;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()?->role?->name !== 'Operador';
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
                $query->whereDate('call_date', '<', now()->subMonths(3));
            })
            ->update(['assigned_operator_id' => null]);

        Notification::make()
            ->title("✅ {$liberadas} empresas liberadas")
            ->body("Acción realizada por {$usuario->name}")
            ->success()
            ->send();
    }

}
