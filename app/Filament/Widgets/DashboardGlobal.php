<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Call;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardGlobal extends BaseWidget
{
    protected static ?string $pollingInterval = null; // no auto-refresh
    
    /**
     * Determina si el widget puede ser visto por el usuario actual.
     * Solo visible para superadmins (role_id = 4)
     */
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role_id === 1 || auth()->user()->role_id === 2 || auth()->user()->role_id === 3 || auth()->user()->role_id === 4;
    }

    protected function getStats(): array
    {
        $now = now();
        $inicioMes = $now->copy()->startOfMonth();
        $inicioMesPasado = $now->copy()->subMonth()->startOfMonth();
        $finMesPasado = $now->copy()->subMonth()->endOfMonth();

        // Ventas este mes y pasado
        $ventasMes = Sale::whereBetween('sale_date', [$inicioMes, $now])->count();
        $ventasMesPasado = Sale::whereBetween('sale_date', [$inicioMesPasado, $finMesPasado])->count();

        // Llamadas este mes y pasado
        $llamadasMes = Call::whereBetween('call_date', [$inicioMes, $now])->count();
        $llamadasMesPasado = Call::whereBetween('call_date', [$inicioMesPasado, $finMesPasado])->count();

        // Ventas por estado
        $tramitadasMes = Sale::where('status', 'tramitada')->whereBetween('sale_date', [$inicioMes, $now])->count();
        $anuladasMes = Sale::where('status', 'anulada')->whereBetween('sale_date', [$inicioMes, $now])->count();
        $incidentadasMes = Sale::where('status', 'incidentada')->whereBetween('sale_date', [$inicioMes, $now])->count();

        // Producto más vendido del mes pasado
        $productoTop = Sale::selectRaw('product_id, COUNT(*) as total')
            ->whereBetween('sale_date', [$inicioMesPasado, $finMesPasado])
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->first();

        $nombreProductoTop = $productoTop?->product?->name ?? 'Sin ventas';

        return [
            Stat::make('🟠 Ventas mes actual', $ventasMes . ' ventas'),
            Stat::make('🟡 Ventas mes pasado', $ventasMesPasado . ' ventas'),
            Stat::make('📞 Llamadas mes actual', $llamadasMes . ' llamadas'),
            Stat::make('📞 Llamadas mes pasado', $llamadasMesPasado . ' llamadas'),
            Stat::make('✅ Tramitadas este mes', $tramitadasMes . ' ventas'),
            Stat::make('❌ Anuladas este mes', $anuladasMes . ' ventas'),
            Stat::make('⚠️ Incidentadas este mes', $incidentadasMes . ' ventas'),
            Stat::make('⭐ Producto top mes pasado', $nombreProductoTop),
        ];
    }
}
