<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GerenciaComparativasWidget extends Widget
{
    protected static string $view = 'filament.widgets.gerencia-comparativas-widget';
    protected static ?int $sort = 1;

    public $ventasPorOperador = [];
    public $ventasPorDia = [];

    public function mount()
    {
        $this->ventasPorOperador = Sale::select(DB::raw('COUNT(*) as total'), 'operator_id')
            ->with('operator')
            ->groupBy('operator_id')
            ->get()
            ->mapWithKeys(fn($row) => [$row->operator->name ?? 'Sin asignar' => $row->total])
            ->toArray();

        $this->ventasPorDia = Sale::select(DB::raw('DATE(created_at) as fecha, COUNT(*) as total'))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(fn($row) => ['fecha' => $row->fecha, 'total' => $row->total])
            ->toArray();
    }

    public static function canView(): bool
    {
        return auth()->check() && in_array(auth()->user()->role_id, [3, 4, 2]);
    }
}
