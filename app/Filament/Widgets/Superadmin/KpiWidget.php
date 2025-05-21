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
