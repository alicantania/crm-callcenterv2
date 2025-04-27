<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\Sale;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class VentasPendientesDeTramitar extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.ventas-pendientes-de-tramitar';
    protected static ?string $navigationLabel = 'Ventas pendientes de tramitar';
    protected static ?string $title = 'Ventas pendientes de tramitar';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                Sale::query()
                    ->where('status', 'pending')
                    ->whereNull('tramitator_id')
            )
            ->columns([
                TextColumn::make('company_name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cif')
                    ->label('CIF')
                    ->searchable(),

                TextColumn::make('sale_date')
                    ->label('Fecha de venta')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('sale_date')
                    ->form([
                        DatePicker::make('sale_date')
                            ->label('Fecha de venta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['sale_date'], fn ($q) => $q->whereDate('sale_date', $data['sale_date']));
                    }),
            ])
            ->actions([
                Action::make('tramitar')
                    ->label('Tramitar')
                    ->color('success')
                    ->icon('heroicon-m-check')
                    ->requiresConfirmation()
                    ->action(fn (Sale $record) => $this->tramitarVenta($record)),
            ])
            ->bulkActions([
                // Podrás añadir acciones en bloque en el futuro
            ]);
    }

    public function tramitarVenta(Sale $sale)
    {
        $sale->update([
            'tramitator_id' => auth()->id(),
            'processing_date' => now(),
            'status' => 'processing',
        ]);

        $this->notify('success', 'Venta asignada para tramitación');
    }
}
