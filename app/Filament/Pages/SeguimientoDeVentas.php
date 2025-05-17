<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Notifications\Notification;
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
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?int $navigationSort = 10;
    
    /**
     * Muestra el número de ventas en seguimiento en el menú lateral
     */
    public static function getNavigationBadge(): ?string
    {
        return Sale::query()
            ->where('status', '!=', 'pendiente')
            ->count() ?: null;
    }
    
    /**
     * Define el color del badge: azul para ventas en seguimiento
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getNavigationBadge();
        return $count ? 'primary' : null;
    }

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
                        $estadoAnterior = $record->status;
                        $nuevoEstado = $data['status'] ?? null;
                        if ($nuevoEstado && $estadoAnterior !== $nuevoEstado) {
                            // Toast voladora
                            Notification::make()
                                ->title("Venta #{$record->id} actualizada")
                                ->body("Tu venta ha pasado a estado: {$nuevoEstado}.")
                                ->icon('heroicon-o-check')
                                ->color('success')
                                ->send();
                            // Notificación persistente (campanita)
                            $operator = $record->operator;
                            if ($operator) {
                                $operator->notify(new \App\Notifications\VentaActualizadaNotification(
                                    $record->id,
                                    $nuevoEstado,
                                    $record->company_name
                                ));
                            }
                        }
                        $record->update([
                            'status' => $data['status'],
                            'tracking_notes' => $data['tracking_notes'],
                        ]);
                    }),
            ]);
    }
}
