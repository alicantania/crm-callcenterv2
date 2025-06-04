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
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Helpers\RoleHelper;

class VentasPendientesDeTramitar extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.ventas-pendientes-de-tramitar';
    protected static ?string $navigationLabel = 'Ventas pendientes de tramitar';
    protected static ?string $title = 'Ventas pendientes de tramitar';
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?int $navigationSort = 20;
    
    /**
     * Muestra el número de ventas pendientes de tramitar como un badge en el menú lateral
     */
    public static function shouldRegisterNavigation(): bool
    {
        return RoleHelper::userHasRole(['Administrador', 'Gerencia']);
    }
    
    /**
     * Muestra el número de ventas pendientes de tramitar como un badge en el menú lateral
     */
    public static function getNavigationBadge(): ?string
    {
        return Sale::query()
            ->where('status', 'pendiente')
            ->whereNull('tramitator_id')
            ->count() ?: null;
    }
    
    /**
     * Define el color del badge: rojo si hay ventas pendientes
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getNavigationBadge();
        return $count ? 'danger' : null;
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                Sale::query()
                    ->where('status', 'pendiente')
                    ->whereNull('tramitator_id')
            )
            ->columns([
                TextColumn::make('company_name')->label('Empresa')->searchable()->sortable(),
                TextColumn::make('cif')->label('CIF')->searchable(),
                TextColumn::make('sale_date')
                    ->label('Fecha de venta')
                    ->date('d-m-Y')
                    ->sortable(),
                TextColumn::make('operator.name')
                    ->label('Operador')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('sale_date')
                    ->form([
                        DatePicker::make('sale_date')->label('Fecha de venta'),
                    ])
                    ->query(fn ($query, array $data) =>
                        $query->when($data['sale_date'], fn ($q) => $q->whereDate('sale_date', $data['sale_date']))
                    ),
            ])
            ->actions([
                Action::make('tramitar')
                    ->label('Tramitar')
                    ->color('success')
                    ->icon('heroicon-m-check')
                    ->form([
                        TextInput::make('contract_number')->label('Número de contrato')->required(),
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'tramitada'   => 'Tramitada',
                                'anulada'     => 'Anulada',
                                'incidentada' => 'Incidentada',
                            ])
                            ->default('tramitada')
                            ->required(),
                        Textarea::make('tracking_notes')
                            ->label('Notas de seguimiento')
                            ->rows(3),
                    ])
                    ->action(function (array $data, Sale $record) {
                        $estadoAnterior = $record->status;
                        $nuevoEstado = $data['status'] ?? null;
                        if ($nuevoEstado && $estadoAnterior !== $nuevoEstado) {
                            \Filament\Notifications\Notification::make()
                                ->title("Venta #{$record->id} actualizada")
                                ->body("Tu venta ha pasado a estado: {$nuevoEstado}.")
                                ->icon('heroicon-o-check')
                                ->color('success')
                                ->send();
                        }
                        // Registrar el tracking del cambio de estado
                        \App\Models\SaleTracking::create([
                            'sale_id'    => $record->id,
                            'old_status' => $record->status, // status antes del update
                            'new_status' => $data['status'],
                            'notes'      => $data['tracking_notes'] ?? null,
                            'changed_by' => auth()->id(),
                        ]);

                        // Actualizar la venta
                        $record->update([
                            'contract_number' => $data['contract_number'],
                            'status'          => $data['status'],
                            'tracking_notes'  => $data['tracking_notes'] ?? null,
                            'tramitator_id'   => auth()->id(),
                            'tramitated_at'   => now(),
                        ]);

                        // Notificar SOLO al operador (creador de la venta)
                        $operator = $record->operator;
                        if ($operator) {
                            $operator->notify(new \App\Notifications\VentaActualizadaNotification(
                                $record->id,
                                $record->status,
                                $record->company_name
                            ));
                        }

                        // Mantener el evento
                        event(new DatabaseNotificationsSent($record->operator));
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Tramitar venta')
                    ->modalSubmitActionLabel('Guardar')
                    ->modalCancelActionLabel('Cancelar'),

                Action::make('devolver')
                    ->label('Devolver al operador')
                    ->color('danger')
                    ->icon('heroicon-m-x-circle')
                    ->form([
                        Textarea::make('observations')
                            ->label('Motivo de la devolución')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (array $data, Sale $record) {
                        // Actualizar el estado a devuelta
                        $record->update([
                            'status'       => 'devuelta',
                            'observations' => $data['observations'],
                        ]);

                        // Notificar al operador sobre la devolución - Método directo más fiable
                        if ($record->operator) {
                            // 1. Notificación usando Filament
                            Notification::make()
                                ->title("‼️ VENTA DEVUELTA - Acción requerida")
                                ->body("Tu venta #{$record->id} ha sido DEVUELTA. Motivo: {$data['observations']}")
                                ->danger()
                                ->persistent()
                                ->sendToDatabase($record->operator);
                            
                            // 2. Doble notificación usando Laravel Notifications para mayor fiabilidad
                            \Illuminate\Support\Facades\Notification::send(
                                $record->operator,
                                new \App\Notifications\VentaActualizadaNotification(
                                    $record->id,
                                    'devuelta',
                                    $record->company_name,
                                    "Motivo de devolución: {$data['observations']}"
                                )
                            );
                            
                            // 3. Emitir evento para notificaciones en tiempo real
                            event(new DatabaseNotificationsSent($record->operator));
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Devolver venta al operador')
                    ->modalSubmitActionLabel('Devolver')
                    ->modalCancelActionLabel('Cancelar'),
            ]);
    }
}
