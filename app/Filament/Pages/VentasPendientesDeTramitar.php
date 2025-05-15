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
                    ->where('status', 'pendiente')
                    ->whereNull('tramitator_id')
            )
            ->columns([
                TextColumn::make('company_name')->label('Empresa')->searchable()->sortable(),
                TextColumn::make('cif')->label('CIF')->searchable(),
                TextColumn::make('sale_date')->label('Fecha de venta')->date()->sortable(),
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
                        // Actualizar la venta
                        $record->update([
                            'contract_number' => $data['contract_number'],
                            'status'          => $data['status'],
                            'tracking_notes'  => $data['tracking_notes'] ?? null,
                            'tramitator_id'   => auth()->id(),
                            'tramitated_at'   => now(),
                        ]);

                        // Enviar notificación al operador en la base de datos
                        DB::table('notifications')->insert([
                            'id' => Str::uuid(),
                            'type' => 'App\\Notifications\\VentaTramitadaNotification',
                            'notifiable_id' => $record->operator->id,
                            'notifiable_type' => 'App\\Models\\User',
                            'data' => json_encode([
                                'title' => "Venta #{$record->id} tramitada",
                                'body' => "Su venta ha pasado a estado: {$record->status}.",
                                'format' => 'filament',
                                'icon' => 'heroicon-o-check',
                                'iconColor' => 'success',
                                'actions' => [],
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // También notificar al tramitador
                        DB::table('notifications')->insert([
                            'id' => Str::uuid(),
                            'type' => 'App\\Notifications\\VentaTramitadaNotification',
                            'notifiable_id' => auth()->id(),
                            'notifiable_type' => 'App\\Models\\User',
                            'data' => json_encode([
                                'title' => "Venta #{$record->id} tramitada",
                                'body' => "La venta ha pasado a estado: {$record->status}.",
                                'format' => 'filament',
                                'icon' => 'heroicon-o-check',
                                'iconColor' => 'success',
                                'actions' => [],
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

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

                        // Notificar al operador sobre la devolución
                        Notification::make()
                            ->title("Venta #{$record->id} devuelta")
                            ->body("Motivo: {$data['observations']}")
                            ->danger()
                            ->sendToDatabase($record->operator);

                        // Emitir evento para notificaciones en tiempo real
                        event(new DatabaseNotificationsSent($record->operator));
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Devolver venta al operador')
                    ->modalSubmitActionLabel('Devolver')
                    ->modalCancelActionLabel('Cancelar'),
            ]);
    }
}
