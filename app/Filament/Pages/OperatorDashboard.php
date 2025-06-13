<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OperatorVentasChart;
use App\Models\Sale;
use App\Models\Call;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

use App\Helpers\RoleHelper;

class OperatorDashboard extends Page
{
    protected static ?string $navigationGroup = 'Operador';
    protected static ?int $navigationSort = 1;
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

    public static function shouldRegisterNavigation(): bool
    {
        return RoleHelper::userHasRole(['Operador', 'Superadmin']);
    }

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
            ->where('operator_id', $userId)
            ->whereBetween('sale_date', [now()->subDays(30), now()])
            ->groupByRaw('DATE(sale_date)')
            ->orderBy('fecha')
            ->pluck('total', 'fecha')
            ->toArray();
    }

    public function getStats(): array
    {
        return [
            Stat::make('📦 Ventas este mes', "{$this->ventasMes} ventas"),
            Stat::make('📅 Ventas hoy', "{$this->ventasHoy} ventas"),
            Stat::make('📞 Llamadas hoy', "{$this->llamadasHoy} llamadas"),
            Stat::make('📞 Llamadas ayer', "{$this->llamadasAyer} llamadas"),
            Stat::make('⏰ Contactos para hoy', "{$this->pendientesHoy} pendientes"),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OperatorVentasChart::class,
        ];
    }
}
