<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\View\View;

class ReporteVentasOperador extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reporte ventas por operador';
    protected static ?string $title = 'Reporte ventas por operador';
    protected static ?int $navigationSort = 120;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->role?->name !== 'Operador';
    }

    public function getViewData(): array
    {
        $mes = date('m');
        $anio = date('Y');
        $operadores = \App\Models\User::where('role_id', 1)
            ->with(['sales.product.businessLine', 'sales.company', 'sales.businessLine'])
            ->get();

        $reporte = $operadores->map(function ($operador) use ($mes, $anio) {
            $ventas = $operador->sales->filter(function ($venta) use ($mes, $anio) {
                return \Carbon\Carbon::parse($venta->sale_date)->format('m') == $mes &&
                       \Carbon\Carbon::parse($venta->sale_date)->format('Y') == $anio;
            });
            $porLinea = $ventas->groupBy(function ($venta) {
                return $venta->businessLine->name ?? ($venta->product->businessLine->name ?? 'Sin línea');
            });
            $detalle = $porLinea->map(function ($ventasLinea, $linea) {
                return [
                    'linea' => $linea,
                    'total_ventas' => $ventasLinea->count(),
                    'total_dinero' => $ventasLinea->sum('amount'),
                    'ventas' => $ventasLinea->map(function ($venta) {
                        $comision = $venta->product && $venta->product->commission_percentage
                            ? round($venta->amount * ($venta->product->commission_percentage / 100), 2)
                            : 0;
                        return [
                            'empresa' => $venta->company->name ?? $venta->company_name ?? '',
                            'cif' => $venta->company->cif ?? $venta->cif ?? '',
                            'producto' => $venta->product->name ?? '',
                            'fecha' => $venta->sale_date,
                            'importe' => $venta->amount,
                            'comision' => $comision,
                        ];
                    }),
                    'total_comision' => $ventasLinea->reduce(function ($carry, $venta) {
                        $comision = $venta->product && $venta->product->commission_percentage
                            ? round($venta->amount * ($venta->product->commission_percentage / 100), 2)
                            : 0;
                        return $carry + $comision;
                    }, 0),
                ];
            });
            return [
                'operador' => $operador,
                'total_ventas' => $ventas->count(),
                'total_dinero' => $ventas->sum('amount'),
                'total_comision' => $ventas->reduce(function ($carry, $venta) {
                    $comision = $venta->product && $venta->product->commission_percentage
                        ? round($venta->amount * ($venta->product->commission_percentage / 100), 2)
                        : 0;
                    return $carry + $comision;
                }, 0),
                'detalle' => $detalle,
            ];
        });
        return compact('mes', 'anio', 'reporte');
    }

    public function getView(): string
    {
        return 'livewire.reportes.ventas-por-operador';
    }
}
