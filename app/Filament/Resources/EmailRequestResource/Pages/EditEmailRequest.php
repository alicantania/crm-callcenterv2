<?php

namespace App\Filament\Resources\EmailRequestResource\Pages;

use App\Filament\Resources\EmailRequestResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;

class EditEmailRequest extends EditRecord
{
    protected static string $resource = EmailRequestResource::class;

    protected function getHeaderActions(): array
    {
        // No actions other than view and save
        return [];
    }

    protected function getFormSchema(): array
    {
        $record = $this->record;
        return [
            Section::make('Información de la solicitud')
                ->schema([
                    Placeholder::make('company')->label('Empresa')->content($record->company->name),
                    Placeholder::make('product')->label('Curso/Producto')->content($record->product->name),
                    Placeholder::make('email_to')->label('Email de destino')->content($record->email_to),
                    Placeholder::make('contact_person')->label('Persona de contacto')->content($record->contact_person),
                    Placeholder::make('requested_by')->label('Solicitado por')->content($record->requestedBy->name),
                ])
                ->columns(2),
            Section::make('Notas del operador')
                ->schema([
                    Placeholder::make('notes')
                        ->label('Notas del operador')
                        ->content(fn () => $record->notes ?? '-'),
                ]),
            Section::make('Notas del administrador')
                ->schema([
                    Textarea::make('admin_notes')
                        ->label('Notas del administrador')
                        ->helperText('Notas añadidas por el administrador durante el procesamiento')
                        ->maxLength(65535)
                        ->required(),
                ]),
            Section::make('Estado')
                ->schema([
                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'pending' => 'Pendiente',
                            'processed' => 'Procesado',
                            'cancelled' => 'Cancelado',
                        ])
                        ->reactive()
                        ->afterStateUpdated(fn (Set $set) => $set('processed_at', now())->set('processed_by_id', auth()->id()))
                        ->required(),
                    Placeholder::make('processed_by')->label('Procesado por')->content(fn () => optional($record->processedBy)->name),
                    Placeholder::make('processed_at')->label('Fecha de procesamiento')->content(fn () => optional($record->processed_at)->format('d/m/Y H:i') ?? '-'),
                    Hidden::make('processed_by_id')->default(fn () => auth()->id()),
                    Hidden::make('processed_at')->default(fn () => now()),
                ])
                ->columns(3),
        ];
    }

    /**
     * After saving the record, notify the operator when processed.
     */
    protected function afterSave(): void
    {
        parent::afterSave();
        if ($this->record->status === 'processed') {
            $requestedBy = $this->record->requestedBy;
            if ($requestedBy) {
                \Filament\Notifications\Notification::make()
                    ->title('✅ Email procesado')
                    ->body('Tu solicitud de email para ' . $this->record->company->name . ' ha sido procesada.')
                    ->sendToDatabase($requestedBy);
            }
        }
    }
}
