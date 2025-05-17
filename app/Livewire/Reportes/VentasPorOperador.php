<?php
namespace App\Livewire\Reportes;

use Livewire\Component;
use App\Models\User;
use Carbon\Carbon;

class VentasPorOperador extends Component
{
    public $mes;
    public $anio;
    public $operadores;
    public $filtroOperador = null;

    public function mount()
    {
        $this->mes = date('m');
        $this->anio = date('Y');
        $this->operadores = User::where('role_id', 1)->with(['sales.product.businessLine', 'sales.company', 'sales.businessLine'])->get();
    }

    public function render()
    {
        $operadores = $this->operadores->map(function ($operador) {
            // Filtrar ventas por mes/año
            $ventas = $operador->sales->filter(function ($venta) {
                return Carbon::parse($venta->sale_date)->format('m') == $this->mes &&
                       Carbon::parse($venta->sale_date)->format('Y') == $this->anio;
            });

            // Agrupar por línea de negocio
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

        return view('livewire.reportes.ventas-por-operador', [
            'reporte' => $operadores,
            'mes' => $this->mes,
            'anio' => $this->anio,
        ]);
    }
}
