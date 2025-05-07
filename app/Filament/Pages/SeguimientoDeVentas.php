<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;

class SeguimientoDeVentas extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static string $view = 'filament.pages.seguimiento-de-ventas';
    protected static ?string $navigationLabel = 'Seguimiento de ventas';
    protected static ?string $title = 'Seguimiento de ventas';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                Sale::query()
                    ->where('status', '!=', 'pendiente')
            )
            ->columns([
                TextColumn::make('company_name')->label('Empresa')->searchable(),
                TextColumn::make('product.name')->label('Curso'),
                TextColumn::make('sale_date')->date()->label('Fecha venta'),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('tramitator.name')->label('Tramitador'),
                TextColumn::make('tramitated_at')->label('Tramitada el')->date(),
            ])
            ->actions([
                Action::make('actualizar_estado')
                    ->label('Actualizar estado')
                    ->icon('heroicon-m-pencil-square')
                    ->form([
                        Select::make('status')
                            ->label('Nuevo estado')
                            ->options([
                                'tramitada' => 'Tramitada',
                                'seguimiento' => 'Seguimiento',
                                'incidentada' => 'Incidentada',
                                'anulada' => 'Anulada',
                                'liquidada' => 'Liquidada',
                            ])
                            ->required(),

                        Textarea::make('tracking_notes')
                            ->label('Notas de seguimiento')
                            ->rows(4)
                            ->placeholder('Observaciones o seguimiento...'),
                    ])
                    ->visible(fn () => in_array(Auth::user()?->role_id, [2, 3, 4])) // Solo admin y gerencia
                    ->action(function (array $data, Sale $record) {
                        $record->update([
                            'status' => $data['status'],
                            'tracking_notes' => $data['tracking_notes'],
                        ]);
                    }),
            ]);
    }
}
