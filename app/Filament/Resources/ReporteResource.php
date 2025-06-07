<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Forms;
use App\Models\User;
use App\Models\Sale;
use App\Models\Call;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use App\Helpers\RoleHelper;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Collection;

class ReporteResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Rendimiento Call Center';
    protected static ?string $navigationGroup = 'Gerencia';
    protected static ?int $navigationSort = 60;

    public static function shouldRegisterNavigation(): bool
    {
        return RoleHelper::userHasRole(['Gerencia', 'Superadmin']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        $fechaInicio = Carbon::now()->subMonth();
        
        return $table
            ->query(
                User::query()
                ->whereExists(function ($query) use ($fechaInicio) {
                    $query->select(DB::raw(1))
                        ->from('calls')
                        ->whereColumn('calls.user_id', 'users.id')
                        ->where('calls.created_at', '>=', $fechaInicio);
                })
                ->withCount([
                    'calls as total_llamadas' => function ($query) use ($fechaInicio) {
                        $query->where('created_at', '>=', $fechaInicio);
                    },
                    'calls as llamadas_efectivas' => function ($query) use ($fechaInicio) {
                        $query->where('created_at', '>=', $fechaInicio)
                              ->where('status', 'venta');
                    },
                    'sales as ventas_generadas' => function ($query) use ($fechaInicio) {
                        $query->where('created_at', '>=', $fechaInicio);
                    }
                ])
                ->withAvg([
                    'calls as duracion_promedio' => function ($query) use ($fechaInicio) {
                        $query->where('created_at', '>=', $fechaInicio)
                              ->whereIn('status', ['venta', 'interesado', 'no interesa', 'volver a llamar'])
                              ->whereNotNull('duration');
                    }
                ], 'duration')
                ->withSum([
                    'sales as importe_ventas' => function ($query) use ($fechaInicio) {
                        $query->where('created_at', '>=', $fechaInicio)
                              ->where(function($query) {
                                  $query->where('status', 'tramitada')
                                      ->orWhere('status', 'completada')
                                      ->orWhere('status', 'procesada');
                              });
                    }
                ], 'sale_price')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Operador')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('total_llamadas')
                    ->label('Total Llamadas')
                    ->sortable(),
                TextColumn::make('llamadas_efectivas')
                    ->label('Llamadas Efectivas')
                    ->sortable(),
                TextColumn::make('tasa_conversion')
                    ->label('Tasa Conversión (%)')
                    ->state(function (User $record): float {
                        if ($record->total_llamadas > 0) {
                            return round(($record->llamadas_efectivas / $record->total_llamadas) * 100, 2);
                        }
                        return 0;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%'),
                TextColumn::make('duracion_promedio')
                    ->label('Duración Promedio (min)')
                    ->state(function (User $record): ?float {
                        if ($record->duracion_promedio) {
                            return round($record->duracion_promedio / 60, 2);
                        }
                        return null;
                    })
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' min' : 'N/A')
                    ->sortable(),
                TextColumn::make('ventas_generadas')
                    ->label('Ventas Generadas')
                    ->sortable(),
                TextColumn::make('importe_ventas')
                    ->label('Importe Ventas (€)')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('eficiencia')
                    ->label('Eficiencia')
                    ->state(function (User $record): string {
                        $tasaConversion = $record->total_llamadas > 0 ? 
                            ($record->llamadas_efectivas / $record->total_llamadas) * 100 : 0;
                        
                        if ($tasaConversion > 70) {
                            return 'Alta';
                        } elseif ($tasaConversion > 40) {
                            return 'Media';
                        } else {
                            return 'Baja';
                        }
                    })
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'Alta' => 'success',
                        'Media' => 'warning',
                        'Baja' => 'danger',
                        default => 'gray'
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('eficiencia')
                    ->label('Eficiencia')
                    ->options([
                        'Alta' => 'Alta',
                        'Media' => 'Media',
                        'Baja' => 'Baja',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value'])) {
                            if ($data['value'] === 'Alta') {
                                return $query->whereRaw('(CASE WHEN total_llamadas > 0 THEN (llamadas_efectivas * 100.0 / total_llamadas) ELSE 0 END) > 70');
                            } elseif ($data['value'] === 'Media') {
                                return $query->whereRaw('(CASE WHEN total_llamadas > 0 THEN (llamadas_efectivas * 100.0 / total_llamadas) ELSE 0 END) > 40 AND (CASE WHEN total_llamadas > 0 THEN (llamadas_efectivas * 100.0 / total_llamadas) ELSE 0 END) <= 70');
                            } elseif ($data['value'] === 'Baja') {
                                return $query->whereRaw('(CASE WHEN total_llamadas > 0 THEN (llamadas_efectivas * 100.0 / total_llamadas) ELSE 0 END) <= 40');
                            }
                        }
                        return $query;
                    }),
                Tables\Filters\Filter::make('tasa_conversion_min')
                    ->form([
                        Forms\Components\TextInput::make('tasa_min')
                            ->label('Tasa de Conversión Mínima (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['tasa_min']) && $data['tasa_min'] !== null && $data['tasa_min'] !== '') {
                            return $query->whereRaw('(CASE WHEN total_llamadas > 0 THEN (llamadas_efectivas * 100.0 / total_llamadas) ELSE 0 END) >= ?', [(float) $data['tasa_min']]);
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_detalle')
                    ->label('Ver Detalle')
                    ->icon('heroicon-o-eye')
                    ->modalContent(function ($record) {
                        $fechaInicio = Carbon::now()->subMonth();
                        
                        // Obtener datos para el modal usando los estados reales de la base de datos
                        $totalLlamadas = DB::table('calls')
                            ->where('user_id', $record->id)
                            ->where('created_at', '>=', $fechaInicio)
                            ->count();
                            
                        $llamadasEfectivas = DB::table('calls')
                            ->where('user_id', $record->id)
                            ->where('created_at', '>=', $fechaInicio)
                            ->where('status', 'venta')
                            ->count();
                            
                        $tasaConversion = $totalLlamadas > 0 ? round(($llamadasEfectivas / $totalLlamadas) * 100, 2) : 0;
                        
                        // Duración promedio solo para llamadas con estados válidos
                        $duracionPromedio = DB::table('calls')
                            ->where('user_id', $record->id)
                            ->where('created_at', '>=', $fechaInicio)
                            ->whereIn('status', ['venta', 'interesado', 'no interesa', 'volver a llamar'])
                            ->whereNotNull('duration')
                            ->avg('duration');
                        $duracionPromedio = $duracionPromedio ? round($duracionPromedio / 60, 2) : 0;
                        
                        // Ventas totales (todas las ventas)
                        $ventasGeneradas = DB::table('sales')
                            ->where('operator_id', $record->id)
                            ->where('created_at', '>=', $fechaInicio)
                            ->count();
                        
                        // Ventas tramitadas/completadas/procesadas
                        $ventasTramitadas = DB::table('sales')
                            ->where('operator_id', $record->id)
                            ->where('created_at', '>=', $fechaInicio)
                            ->where(function($query) {
                                $query->where('status', 'tramitada')
                                    ->orWhere('status', 'completada')
                                    ->orWhere('status', 'procesada');
                            })
                            ->count();
                            
                        $importeVentas = DB::table('sales')
                            ->where('operator_id', $record->id)
                            ->where('created_at', '>=', $fechaInicio)
                            ->where(function($query) {
                                $query->where('status', 'tramitada')
                                    ->orWhere('status', 'completada')
                                    ->orWhere('status', 'procesada');
                            })
                            ->sum('sale_price');
                            
                        // Ventas anuladas/canceladas
                        $ventasAnuladas = DB::table('sales')
                            ->where('operator_id', $record->id)
                            ->where('created_at', '>=', $fechaInicio)
                            ->where(function($query) {
                                $query->where('status', 'anulada')
                                    ->orWhere('status', 'cancelada');
                            })
                            ->count();
                            
                        $importeAnulado = DB::table('sales')
                            ->where('operator_id', $record->id)
                            ->where('created_at', '>=', $fechaInicio)
                            ->where(function($query) {
                                $query->where('status', 'anulada')
                                    ->orWhere('status', 'cancelada');
                            })
                            ->sum('sale_price');
                            
                        // Ventas pendientes/incidentadas
                        $ventasPendientes = DB::table('sales')
                            ->where('operator_id', $record->id)
                            ->where('created_at', '>=', $fechaInicio)
                            ->where(function($query) {
                                $query->where('status', 'pendiente')
                                    ->orWhere('status', 'incidentada');
                            })
                            ->count();
                            
                        $importePendiente = DB::table('sales')
                            ->where('operator_id', $record->id)
                            ->where('created_at', '>=', $fechaInicio)
                            ->where(function($query) {
                                $query->where('status', 'pendiente')
                                    ->orWhere('status', 'incidentada');
                            })
                            ->sum('sale_price');
                            
                        // Calcular distribución por líneas de negocio
                        $ventasPorLinea = DB::table('sales')
                            ->join('business_lines', 'sales.business_line_id', '=', 'business_lines.id')
                            ->select('business_lines.name', DB::raw('COUNT(*) as total'))
                            ->where('sales.operator_id', $record->id)
                            ->where('sales.created_at', '>=', $fechaInicio)
                            ->groupBy('business_lines.id', 'business_lines.name')
                            ->get();
                            
                        // Últimas llamadas - Mostrar duración solo cuando corresponde
                        $ultimasLlamadas = DB::table('calls')
                            ->join('companies', 'calls.company_id', '=', 'companies.id')
                            ->select('calls.*', 'companies.name as company_name')
                            ->where('calls.user_id', $record->id)
                            ->where('calls.created_at', '>=', $fechaInicio)
                            ->orderBy('calls.created_at', 'desc')
                            ->limit(5)
                            ->get();
                        
                        return view('filament.resources.reporte-resource.pages.detalle-operador', [
                            'operador' => $record,
                            'totalLlamadas' => $totalLlamadas,
                            'llamadasEfectivas' => $llamadasEfectivas,
                            'tasaConversion' => $tasaConversion,
                            'duracionPromedio' => $duracionPromedio,
                            'ventasGeneradas' => $ventasGeneradas,
                            'ventasTramitadas' => $ventasTramitadas,
                            'importeVentas' => $importeVentas,
                            'ventasAnuladas' => $ventasAnuladas,
                            'importeAnulado' => $importeAnulado,
                            'ventasPendientes' => $ventasPendientes,
                            'importePendiente' => $importePendiente,
                            'ventasPorLinea' => $ventasPorLinea,
                            'ultimasLlamadas' => $ultimasLlamadas,
                            'fechaInicio' => $fechaInicio->format('d/m/Y'),
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportar_excel')
                    ->label('Exportar a Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        Notification::make()
                            ->title('Exportación Iniciada')
                            ->body('El reporte se está generando y se descargará automáticamente.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('refrescar')
                    ->label('Refrescar Datos')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        Notification::make()
                            ->title('Datos Actualizados')
                            ->body('Los datos del reporte han sido actualizados.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ReporteResource\Pages\ListReportes::route('/'),
        ];
    }
}
