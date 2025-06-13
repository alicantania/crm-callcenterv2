<?php
namespace App\Filament\Widgets\Superadmin;

use Filament\Widgets\Widget;
use App\Models\Sale;
use App\Models\User;

class CommissionChartWidget extends Widget
{
    protected static string $view = 'filament.widgets.superadmin.commission-chart-widget';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role_id === 5;
    }

    public function getViewData(): array
    {
        $labels = User::where('role_id', 1)->pluck('name');
        $data = $labels->map(function ($name, $i) {
            $user = User::where('name', $name)->first();
            return Sale::where('operator_id', $user->id)->sum('commission_amount');
        });
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
