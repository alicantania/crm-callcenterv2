<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (
            $this->record->status !== 'tramitada' &&
            $data['status'] === 'tramitada' &&
            Auth::user()?->role_id !== 1
        ) {
            $data['tramitated_at'] = now();
            $data['tramitator_id'] = Auth::id();
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
