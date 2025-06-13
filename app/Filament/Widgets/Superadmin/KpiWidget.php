<?php
namespace App\Filament\Widgets\Superadmin;

use Filament\Widgets\Widget;
use App\Models\Company;
use App\Models\Call;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Carbon;

class KpiWidget extends Widget
{
    protected static string $view = 'filament.widgets.superadmin.kpi-widget';
    
    /**
     * Determina si el widget puede ser visto por el usuario actual.
     * Solo visible para superadmins (role_id = 4)
     */
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role_id === 1 || auth()->user()->role_id === 2;
    }

    public function getViewData(): array
    {
        $today = Carbon::today();
        return [
            'totalCompanies' => Company::count(),
            'callsToday' => Call::whereDate('call_date', $today)->count(),
            'pendingSales' => Sale::where('status', 'pendiente')->count(),
            'activeOperators' => User::where('role_id', 1)->where('active', true)->count(),
        ];
    }
}
