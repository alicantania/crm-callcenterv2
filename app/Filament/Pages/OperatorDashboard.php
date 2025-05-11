<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OperatorVentasChart;
use App\Models\Sale;
use App\Models\Call;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OperatorDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.operator-dashboard';
    protected static ?string $navigationLabel = 'Resumen del operador';
    protected static ?string $title = 'Dashboard operador';

    public $ventasMes = 0;
    public $ventasHoy = 0;
    public $ventasUltimoMes = [];
    public $llamadasHoy = 0;
    public $llamadasAyer = 0;
    public $pendientesHoy = 0;
    public array $ventasPorDia = [];


    public function mount(): void
    {
        $userId = Auth::id();

        $this->ventasMes = Sale::where('operator_id', $userId)
            ->whereMonth('sale_date', now()->month)
            ->count();

        $this->ventasHoy = Sale::where('operator_id', $userId)
            ->whereDate('sale_date', today())
            ->count();

        $this->llamadasHoy = Call::where('user_id', $userId)
            ->whereDate('call_date', today())
            ->count();

        $this->llamadasAyer = Call::where('user_id', $userId)
            ->whereDate('call_date', today()->subDay())
            ->count();

        $this->pendientesHoy = Call::where('user_id', $userId)
            ->whereDate('call_date', today())
            ->where('status', 'volver a llamar')
            ->count();

        $this->ventasPorDia = Sale::selectRaw('DATE(sale_date) as fecha, COUNT(*) as total')
            ->where('operator_id', Auth::id())
            ->whereBetween('sale_date', [now()->subDays(30), now()])
            ->groupByRaw('DATE(sale_date)')
            ->pluck('total', 'fecha')
            ->toArray();
    }

    public function getStats(): array
    {
        return [
            Stat::make('📦 Ventas este mes', $this->ventasMes),
            Stat::make('📅 Ventas hoy', $this->ventasHoy),
            Stat::make('📞 Llamadas hoy', $this->llamadasHoy),
            Stat::make('📞 Llamadas ayer', $this->llamadasAyer),
            Stat::make('⏰ Pendientes hoy', $this->pendientesHoy),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OperatorVentasChart::class,
        ];
    }
}
