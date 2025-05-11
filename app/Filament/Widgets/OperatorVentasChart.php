<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class OperatorVentasChart extends ChartWidget
{
    protected static ?string $heading = '📈 Ventas últimos 30 días';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $userId = Auth::id();

        $ventas = Sale::query()
            ->where('operator_id', $userId)
            ->whereBetween('sale_date', [now()->subDays(30), now()])
            ->selectRaw('DATE(sale_date) as date, COUNT(*) as total')
            ->groupByRaw('DATE(sale_date)')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        foreach ($ventas as $venta) {
            $labels[] = \Carbon\Carbon::parse($venta->date)->format('d M');
            $data[] = $venta->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ventas',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)', // azul translúcido
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
