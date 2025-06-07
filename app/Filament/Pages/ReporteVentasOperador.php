<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Helpers\RoleHelper;
use App\Models\User;
use App\Models\Sale;
use App\Exports\VentasOperadorExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReporteVentasOperador extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reporte ventas por operador';
    protected static ?string $title = 'Reporte ventas por operador';
    protected static ?int $navigationSort = 120;
    
    // Propiedades para los filtros
    public ?string $selectedOperador = null;
    public ?string $selectedMes = null;
    public ?string $selectedAnio = null;
    public bool $mostrarDetalle = true;
    
    public function mount(): void
    {
        $this->selectedMes = now()->format('m');
        $this->selectedAnio = now()->format('Y');
        $this->form->fill();
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && (RoleHelper::userHasRole(['Gerencia']) || RoleHelper::userHasRole(['Superadmin']));
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedOperador')
                    ->label('Operador')
                    ->options(User::where('role_id', 1)->pluck('name', 'id'))
                    ->placeholder('Todos los operadores')
                    ->live(),
                
                Select::make('selectedMes')
                    ->label('Mes')
                    ->options([
                        '01' => 'Enero',
                        '02' => 'Febrero',
                        '03' => 'Marzo',
                        '04' => 'Abril',
                        '05' => 'Mayo',
                        '06' => 'Junio',
                        '07' => 'Julio',
                        '08' => 'Agosto',
                        '09' => 'Septiembre',
                        '10' => 'Octubre',
                        '11' => 'Noviembre',
                        '12' => 'Diciembre',
                    ])
                    ->default(now()->format('m'))
                    ->live(),
                
                Select::make('selectedAnio')
                    ->label('Año')
                    ->options(function () {
                        $years = [];
                        $currentYear = (int)now()->format('Y');
                        for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                            $years[$i] = (string)$i;
                        }
                        return $years;
                    })
                    ->default(now()->format('Y'))
                    ->live(),
                
                Select::make('mostrarDetalle')
                    ->label('Mostrar')
                    ->options([
                        true => 'Detalle completo',
                        false => 'Solo resumen'
                    ])
                    ->default(true)
                    ->live(),
            ])
            ->columns(4);
    }
    
    public function exportarExcel()
    {
        $data = $this->getViewData();
        $mes = $data['mes'];
        $anio = $data['anio'];
        $reporte = $data['reporte'];
        $mostrarDetalle = $data['mostrarDetalle'];
        
        $nombreMes = Carbon::createFromDate($anio, $mes, 1)->locale('es')->monthName;
        $fileName = "ventas_operadores_{$nombreMes}_{$anio}.xlsx";
        
        // Notificar que la exportación está en proceso
        Notification::make()
            ->title('Exportando datos')
            ->body('El reporte se está generando y se descargará automáticamente.')
            ->success()
            ->send();
        
        return Excel::download(
            new VentasOperadorExport($reporte, $mes, $anio, $mostrarDetalle),
            $fileName
        );
    }
    
    public function getViewData(): array
    {
        $mes = $this->selectedMes ?: now()->format('m');
        $anio = $this->selectedAnio ?: now()->format('Y');
        
        $operadoresQuery = User::where('role_id', 1);
            
        if ($this->selectedOperador) {
            $operadoresQuery->where('id', $this->selectedOperador);
        }
        
        $operadores = $operadoresQuery->get();
        
        $reporte = collect();
        
        foreach ($operadores as $operador) {
            $ventas = Sale::where('operator_id', $operador->id)
                ->whereYear('sale_date', $anio)
                ->whereMonth('sale_date', $mes)
                ->with(['product.businessLine', 'company', 'businessLine'])
                ->get();
            
            $porLinea = $ventas->groupBy(function ($venta) {
                return $venta->businessLine->name ?? ($venta->product->businessLine->name ?? 'Sin línea');
            });
            
            $detalle = collect();
            $totalDineroOperador = 0;
            $totalComisionOperador = 0;
            $totalVentasOperador = 0;
            
            foreach ($porLinea as $linea => $ventasLinea) {
                $ventasDetalle = collect();
                $totalDineroLinea = 0;
                $totalComisionLinea = 0;
                $totalVentasLinea = 0;
                
                foreach ($ventasLinea as $venta) {
                    $comision = $venta->commission_amount ?: 
                               ($venta->product && $venta->product->commission_percentage
                                ? round(($venta->sale_price ?: 0) * ($venta->product->commission_percentage / 100), 2)
                                : 0);
                    
                    $importe = $venta->sale_price ?: 0;
                    $contabilizar = 0; // 0: standby, 1: suma, -1: resta
                    
                    // Determinar si la venta suma, resta o está en standby según su estado
                    switch (strtolower($venta->status)) {
                        case 'tramitada':
                        case 'completada':
                        case 'procesada':
                            $contabilizar = 1; // Suma
                            $totalDineroLinea += $importe;
                            $totalComisionLinea += $comision;
                            $totalVentasLinea += 1;
                            break;
                        case 'anulada':
                        case 'cancelada':
                            $contabilizar = -1; // Resta
                            $totalDineroLinea -= $importe;
                            $totalComisionLinea -= $comision;
                            $totalVentasLinea -= 1;
                            break;
                        case 'pendiente':
                        case 'incidentada':
                        default:
                            $contabilizar = 0; // Standby
                            break;
                    }
                    
                    $ventasDetalle->push([
                        'id' => $venta->id,
                        'empresa' => $venta->company->name ?? $venta->company_name ?? 'Sin empresa',
                        'cif' => $venta->company->cif ?? $venta->cif ?? 'Sin CIF',
                        'producto' => $venta->product->name ?? 'Sin producto',
                        'fecha' => $venta->sale_date,
                        'importe' => $importe,
                        'comision' => $comision,
                        'status' => $venta->status ?? 'pendiente',
                        'contabilizar' => $contabilizar,
                    ]);
                }
                
                $detalle->put($linea, [
                    'linea' => $linea,
                    'total_ventas' => $totalVentasLinea,
                    'total_dinero' => $totalDineroLinea,
                    'ventas' => $ventasDetalle,
                    'total_comision' => $totalComisionLinea,
                ]);
                
                $totalDineroOperador += $totalDineroLinea;
                $totalComisionOperador += $totalComisionLinea;
                $totalVentasOperador += $totalVentasLinea;
            }
            
            $reporte->push([
                'operador' => $operador,
                'total_ventas' => $totalVentasOperador,
                'total_dinero' => $totalDineroOperador,
                'total_comision' => $totalComisionOperador,
                'detalle' => $detalle,
            ]);
        }
        
        return [
            'mes' => $mes,
            'anio' => $anio,
            'reporte' => $reporte,
            'mostrarDetalle' => $this->mostrarDetalle,
            'nombreMes' => Carbon::createFromDate($anio, $mes, 1)->locale('es')->monthName,
            'estados_colores' => [
                'tramitada' => 'green',
                'completada' => 'green',
                'procesada' => 'green',
                'anulada' => 'red',
                'cancelada' => 'red',
                'pendiente' => 'yellow',
                'incidentada' => 'orange',
            ],
        ];
    }
    
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('exportar_excel')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action('exportarExcel'),
        ];
    }

    public function getView(): string
    {
        return 'livewire.reportes.ventas-por-operador';
    }
}
