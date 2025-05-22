<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\Widget;
use App\Models\Sale;
use App\Models\User;
use App\Models\BusinessLine;
use Illuminate\Support\Facades\Auth;
use App\Helpers\RoleHelper;

class GerenciaDashboard extends Page
{
    /**
     * Alterna la selección de una línea de negocio en el array selectedBusinessLines
     */
    public function toggleBusinessLine($id)
    {
        $selected = $this->selectedBusinessLines ?? [];
        if (in_array($id, $selected)) {
            // Quita si ya está seleccionado
            $this->selectedBusinessLines = array_values(array_diff($selected, [$id]));
        } else {
            // Añade si no está seleccionado
            $this->selectedBusinessLines[] = $id;
        }
    }

    public ?int $selectedUserId = null;
    public array $users = [];

    public array $businessLines = [];
    public array $selectedBusinessLines = [];
    public array $products = [];
    public array $selectedProducts = [];
    public string $groupBy = 'month';
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount()
    {
        // Cargar todos los operadores y administradores
        $this->users = User::whereIn('role_id', [1,2,3,4])->orderBy('name')->get(['id','name','role_id'])->toArray();
        // Cargar todas las líneas de negocio
        $this->businessLines = BusinessLine::orderBy('name')->get(['id','name'])->toArray();
        // Cargar todos los productos
        $this->products = \App\Models\Product::orderBy('name')->get(['id','name'])->toArray();
    }
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.gerencia-dashboard';
    protected static ?string $navigationLabel = 'Dashboard Gerencia';
    protected static ?string $title = 'Dashboard Gerencia';
    protected static ?int $navigationSort = 10;

    public static function shouldRegisterNavigation(): bool
    {
        // Solo visible para Gerencia y Superadmin
        return RoleHelper::userHasRole(['Gerencia']);
    }

    public function updated($property)
    {
        if (in_array($property, [
            'selectedUserId',
            'selectedBusinessLines',
            'selectedProducts',
            'startDate',
            'endDate',
            'groupBy',
        ])) {
            $this->emitChartData();
        }
    }

    public function emitChartData()
    {
        $this->dispatch('chartDataUpdated', $this->getChartData());
    }

    public function getStats(): array
    {
        // Preparar query base
        $userId = $this->selectedUserId;
        $salesQuery = Sale::query();
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                if ($user->role_id === 1) {
                    $salesQuery->where('operator_id', $userId);
                } elseif (in_array($user->role_id, [2,3,4])) {
                    $salesQuery->where('tramitator_id', $userId);
                }
            }
        }
        // Filtrar por líneas de negocio seleccionadas
        if (!empty($this->selectedBusinessLines)) {
            $salesQuery->whereHas('product.businessLine', function ($query) {
                $query->whereIn('id', $this->selectedBusinessLines);
            });
        }
        // Filtrar por productos seleccionados
        if (!empty($this->selectedProducts)) {
            $salesQuery->whereIn('product_id', $this->selectedProducts);
        }
        // Filtrar por fechas
        if ($this->startDate) {
            $salesQuery->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $salesQuery->whereDate('created_at', '<=', $this->endDate);
        }
        $allSales = $salesQuery->get();
        $totalSales = $allSales->count();
        $salesToday = $allSales->where('created_at', '>=', now()->startOfDay())->count();
        $pendingSales = $allSales->where('status', 'pendiente')->count();
        $returnedSales = $allSales->where('status', 'devuelta')->count();
        $totalAmount = $allSales->sum(fn($sale) => optional($sale->product)->price ?? 0);
        $totalCommissions = $allSales->sum(function($sale) {
            $product = $sale->product;
            if (!$product) return 0;
            $commission = 0;
            if (($product->commission_type ?? 'porcentaje') === 'porcentaje') {
                $commission = ($product->commission_value ?? 0) * ($product->price ?? 0) / 100;
            } else {
                $commission = $product->commission_value ?? 0;
            }
            return $commission;
        });
        $operators = User::where('role_id', 1)->count();
        $admins = User::whereIn('role_id', [2,4])->count();
        return [
            'total_sales' => $totalSales,
            'sales_today' => $salesToday,
            'pending_sales' => $pendingSales,
            'returned_sales' => $returnedSales,
            'total_amount' => number_format($totalAmount, 2, ',', '.') . ' €',
            'total_commissions' => number_format($totalCommissions, 2, ',', '.') . ' €',
            'operators' => $operators,
            'admins' => $admins,
        ];
    }

    /**
     * Datos para gráficas dinámicas (ventas y comisiones agrupadas)
     */
    public function getChartData(): array
    {
        $groupBy = $this->groupBy;
        $userId = $this->selectedUserId;
        $salesQuery = Sale::query();
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                if ($user->role_id === 1) {
                    $salesQuery->where('operator_id', $userId);
                } elseif (in_array($user->role_id, [2,3,4])) {
                    $salesQuery->where('tramitator_id', $userId);
                }
            }
        }
        if (!empty($this->selectedBusinessLines)) {
            $salesQuery->whereHas('product.businessLine', function ($query) {
                $query->whereIn('id', $this->selectedBusinessLines);
            });
        }
        if (!empty($this->selectedProducts)) {
            $salesQuery->whereIn('product_id', $this->selectedProducts);
        }
        if ($this->startDate) {
            $salesQuery->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $salesQuery->whereDate('created_at', '<=', $this->endDate);
        }
        $sales = $salesQuery->get();
        $ventas = [];
        $comisiones = [];
        if ($groupBy === 'year') {
            $ventas = $sales->groupBy(fn($sale) => \Carbon\Carbon::parse($sale->created_at)->format('Y'));
        } else {
            $ventas = $sales->groupBy(fn($sale) => \Carbon\Carbon::parse($sale->created_at)->format('Y-m'));
        }
        $ventasPorPeriodo = [];
        $comisionesPorPeriodo = [];
        foreach ($ventas as $periodo => $ventasDelPeriodo) {
            $ventasPorPeriodo[$periodo] = $ventasDelPeriodo->count();
            $comisionesPorPeriodo[$periodo] = $ventasDelPeriodo->sum(function($sale) {
                $product = $sale->product;
                if (!$product) return 0;
                $commission = 0;
                if (($product->commission_type ?? 'porcentaje') === 'porcentaje') {
                    $commission = ($product->commission_value ?? 0) * ($product->price ?? 0) / 100;
                } else {
                    $commission = $product->commission_value ?? 0;
                }
                return $commission;
            });
        }
        return [
            'labels' => array_keys($ventasPorPeriodo),
            'ventas' => array_values($ventasPorPeriodo),
            'comisiones' => array_map(fn($c) => round($c, 2), array_values($comisionesPorPeriodo)),
        ];
    }
}
