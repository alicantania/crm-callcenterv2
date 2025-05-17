<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Forms;
use App\Models\Sale;
use App\Models\Call;
use App\Models\User;
use App\Models\BusinessLine;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\SaleResource;
use Filament\Notifications\Notification;

class ReporteResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reportes';
    protected static ?string $navigationGroup = 'Gerencia';
    protected static ?int $navigationSort = 60;

    public static function shouldRegisterNavigation(): bool
    {
        // Solo visible para Gerencia, Admin y SuperAdmin
        return Auth::check() && in_array(Auth::user()->role_id, [3, 4, 2]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Solo lectura, no edición
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('operator.name')
                    ->label('Operador')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Fecha Venta')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Importe (€)')
                    ->money('EUR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('EUR')),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Comisión (€)')
                    ->money('EUR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('EUR')),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'pendiente' => 'gray',
                        'tramitada' => 'success',
                        'devuelta' => 'danger', 
                        default => 'warning'
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.businessLine.name')
                    ->label('Línea de Negocio')
                    ->sortable(),
            ])
            ->groups([
                Tables\Grouping\Group::make('operator.name')
                    ->label('Agrupar por Operador')
                    ->collapsible(),
                Tables\Grouping\Group::make('product.businessLine.name')
                    ->label('Agrupar por Línea de Negocio')
                    ->collapsible(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('operator_id')
                    ->label('Operador')
                    ->options(User::where('role_id', 1)->pluck('name', 'id')->toArray()),
                Tables\Filters\SelectFilter::make('mes')
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
                    ->query(function ($query, $data) {
                        if (!empty($data['value'])) {
                            $query->whereMonth('sale_date', $data['value']);
                        }
                    }),
                Tables\Filters\SelectFilter::make('business_line_id')
                    ->label('Línea de Negocio')
                    ->options(BusinessLine::pluck('name', 'id')->toArray())
                    ->query(function ($query, $data) {
                        if (!empty($data['value'])) {
                            $query->where(function ($sub) use ($data) {
                                $sub->where('business_line_id', $data['value'])
                                    ->orWhereHas('product', function ($q) use ($data) {
                                        $q->where('business_line_id', $data['value']);
                                    });
                            });
                        }
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'tramitada' => 'Tramitada',
                        'devuelta' => 'Devuelta',
                    ]),
                Tables\Filters\Filter::make('sale_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Desde'),
                        Forms\Components\DatePicker::make('to')->label('Hasta'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['from']) {
                            $query->whereDate('sale_date', '>=', $data['from']);
                        }
                        if ($data['to']) {
                            $query->whereDate('sale_date', '<=', $data['to']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_detalles')
                    ->label('Ver detalles')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Sale $record): string => SaleResource::getUrl('view', ['record' => $record])),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportar_excel')
                    ->label('Exportar a Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(route('filament.dashboard.resources.reportes.index', ['tableActionExport' => true, 'format' => 'xlsx'])),
                Tables\Actions\Action::make('exportar_pdf')
                    ->label('Exportar a PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->url(route('filament.dashboard.resources.reportes.index', ['tableActionExport' => true, 'format' => 'pdf'])),
            ])
            ->bulkActions([
                Tables\Actions\ExportBulkAction::make()
                    ->formats([
                        'csv' => 'CSV',
                        'xlsx' => 'Excel',
                        'pdf' => 'PDF',
                    ])
            ])
            ->defaultSort('created_at', 'desc');
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
