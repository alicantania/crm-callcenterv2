<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\SaleTracking;

class EditSale extends EditRecord
{
    public function mount($record): void
    {
        parent::mount($record);
        if (\Illuminate\Support\Facades\Auth::user()?->role_id === 1) {
            $this->redirect(
                \App\Filament\Resources\SaleResource::getUrl('view', ['record' => $this->record->getKey()])
            );
        }
    }
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $nuevoEstado = $data['status'] ?? null;
        $estadoAnterior = $this->record->status;

        if (
            $this->record->status !== 'tramitada' &&
            $nuevoEstado === 'tramitada' &&
            Auth::user()?->role_id !== 1
        ) {
            $data['tramitated_at'] = now();
            $data['tramitator_id'] = Auth::id();
        }

        // Lanzar toast y registrar tracking SIEMPRE que cambie el estado
        if ($nuevoEstado && $estadoAnterior !== $nuevoEstado) {
            // Guardar registro en SaleTracking
            SaleTracking::create([
                'sale_id'     => $this->record->id,
                'old_status'  => $estadoAnterior,
                'new_status'  => $nuevoEstado,
                'notes'       => $data['tracking_notes'] ?? null,
                'changed_by'  => Auth::id(),
            ]);

            \Filament\Notifications\Notification::make()
                ->title("Venta #{$this->record->id} actualizada")
                ->body("Tu venta ha pasado a estado: {$nuevoEstado}.")
                ->icon('heroicon-o-check')
                ->color('success')
                ->send();
        }

        return $data;
    }


    protected function getFormSchema(): array
    {
        // SOLO roles distintos de operador (admin, gerencia, superadmin)
        if (!in_array(Auth::user()->role_id, [1])) {
            return [
                Forms\Components\Section::make('🛠️ Gestión de la Venta')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado de la venta')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'tramitada' => 'Tramitada',
                                'seguimiento' => 'Seguimiento',
                                'incidentada' => 'Incidentada',
                                'anulada' => 'Anulada',
                                'liquidada' => 'Liquidada',
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('contract_number')
                            ->label('Número de contrato')
                            ->visible(fn () => $this->record->status === 'pendiente')
                            ->required(fn () => $this->record->status === 'pendiente')
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('tracking_notes')
                            ->label('Notas internas de seguimiento')
                            ->autosize()
                            ->rows(4)
                            ->placeholder('Observaciones del estado actual...')
                            ->columnSpan(2),
                    ]),
            ];
        }

        return [];
    }
}
