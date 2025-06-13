<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Call;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RendimientoCallCenterStats extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    
    /**
     * Determina si el widget puede ser visto por el usuario actual.
     * Solo visible para superadmins (role_id = 4)
     */
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role_id === 4;
    }
    
    protected function getStats(): array
    {
        // Obtener el período seleccionado (por defecto último mes)
        $periodo = request()->get('tableFilters.periodo.value') ?? 'mes';
        $fechaInicio = null;
        
        switch ($periodo) {
            case 'semana':
                $fechaInicio = Carbon::now()->subWeek();
                $periodoTexto = 'última semana';
                break;
            case 'mes':
                $fechaInicio = Carbon::now()->subMonth();
                $periodoTexto = 'último mes';
                break;
            case 'trimestre':
                $fechaInicio = Carbon::now()->subMonths(3);
                $periodoTexto = 'último trimestre';
                break;
            case 'año':
                $fechaInicio = Carbon::now()->subYear();
                $periodoTexto = 'último año';
                break;
            default:
                $fechaInicio = Carbon::now()->subMonth();
                $periodoTexto = 'último mes';
        }
        
        // Total de llamadas
        $totalLlamadas = Call::where('created_at', '>=', $fechaInicio->format('Y-m-d H:i:s'))->count();
        
        // Llamadas efectivas
        $llamadasEfectivas = Call::where('created_at', '>=', $fechaInicio->format('Y-m-d H:i:s'))
            ->where('status', 'efectiva')
            ->count();
        
        // Tasa de conversión
        $tasaConversion = $totalLlamadas > 0 
            ? round(($llamadasEfectivas / $totalLlamadas) * 100, 2) 
            : 0;
        
        // Duración promedio de llamadas
        $duracionPromedio = Call::where('created_at', '>=', $fechaInicio->format('Y-m-d H:i:s'))
            ->whereNotNull('duration')
            ->avg('duration');
            
        // Convertir a minutos si está en segundos
        $duracionPromedio = round($duracionPromedio / 60, 2);
        
        // Ventas generadas
        $ventasGeneradas = Sale::where('created_at', '>=', $fechaInicio->format('Y-m-d H:i:s'))->count();
        
        // Importe total de ventas
        $importeVentas = Sale::where('created_at', '>=', $fechaInicio->format('Y-m-d H:i:s'))
            ->where(function($query) {
                $query->where('status', 'tramitada')
                      ->orWhere('status', 'completada')
                      ->orWhere('status', 'procesada');
            })
            ->sum('sale_price');
        
        // Importe de ventas anuladas (resta)
        $importeVentasAnuladas = Sale::where('created_at', '>=', $fechaInicio->format('Y-m-d H:i:s'))
            ->where(function($query) {
                $query->where('status', 'anulada')
                      ->orWhere('status', 'cancelada');
            })
            ->sum('sale_price');
        
        // Importe neto (ventas tramitadas - anuladas)
        $importeNeto = $importeVentas - $importeVentasAnuladas;
        
        try {
            // Operador más eficiente
            $operadorEficiente = DB::table('calls')
                ->select([
                    'users.name as nombre',
                    DB::raw('COUNT(calls.id) as total_llamadas'),
                    DB::raw('SUM(CASE WHEN calls.status = \'efectiva\' THEN 1 ELSE 0 END) as llamadas_efectivas'),
                    DB::raw('ROUND((SUM(CASE WHEN calls.status = \'efectiva\' THEN 1 ELSE 0 END) / NULLIF(COUNT(calls.id), 0)) * 100, 2) as tasa_conversion')
                ])
                ->join('users', 'calls.user_id', '=', 'users.id')
                ->where('calls.created_at', '>=', $fechaInicio->format('Y-m-d H:i:s'))
                ->groupBy('users.id', 'users.name')
                ->havingRaw('COUNT(calls.id) > 10') // Mínimo 10 llamadas para considerar
                ->orderBy('tasa_conversion', 'desc')
                ->first();
        } catch (\Exception $e) {
            // Si hay un error, simplemente establecemos el operador como null
            $operadorEficiente = null;
        }
        
        return [
            Stat::make('Total Llamadas', $totalLlamadas)
                ->description("En el {$periodoTexto}")
                ->descriptionIcon('heroicon-m-phone')
                ->chart([
                    $totalLlamadas * 0.7, 
                    $totalLlamadas * 0.8, 
                    $totalLlamadas * 0.9, 
                    $totalLlamadas
                ])
                ->color('primary'),
                
            Stat::make('Tasa de Conversión', "{$tasaConversion}%")
                ->description($tasaConversion > 50 ? 'Excelente rendimiento' : 'Rendimiento normal')
                ->descriptionIcon($tasaConversion > 50 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([
                    $tasaConversion * 0.7, 
                    $tasaConversion * 0.8, 
                    $tasaConversion * 0.9, 
                    $tasaConversion
                ])
                ->color($tasaConversion > 50 ? 'success' : 'warning'),
                
            Stat::make('Ventas Generadas', $ventasGeneradas)
                ->description("Importe neto: " . number_format($importeNeto, 2, ',', '.') . " €")
                ->descriptionIcon('heroicon-m-currency-euro')
                ->chart([
                    $ventasGeneradas * 0.7, 
                    $ventasGeneradas * 0.8, 
                    $ventasGeneradas * 0.9, 
                    $ventasGeneradas
                ])
                ->color('success'),
                
            Stat::make('Duración Promedio', "{$duracionPromedio} min")
                ->description("Por llamada")
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray'),
                
            Stat::make('Operador Destacado', $operadorEficiente ? $operadorEficiente->nombre : 'N/A')
                ->description($operadorEficiente ? "Conversión: {$operadorEficiente->tasa_conversion}%" : 'Sin datos suficientes')
                ->descriptionIcon('heroicon-m-user')
                ->color('primary'),
        ];
    }
}
