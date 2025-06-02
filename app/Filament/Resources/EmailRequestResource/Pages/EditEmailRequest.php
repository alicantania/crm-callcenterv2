<?php

namespace App\Filament\Resources\EmailRequestResource\Pages;

use App\Filament\Resources\EmailRequestResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;
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
    
    protected function authorizeAccess(): void
    {
        // Only allow admins, gerencia, and superadmins to edit email requests
        abort_unless(
            auth()->user()?->role?->name !== 'Operador',
            403
        );
        
        parent::authorizeAccess();
    }

    protected function getHeaderActions(): array
    {
        // Add save action to process the request
        return [
            \Filament\Actions\Action::make('save')
                ->label('Guardar')
                ->submit('save')
                ->keyBindings(['mod+s'])
        ];
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
                        ->afterStateUpdated(function (string $state, Set $set) {
                            if ($state === 'processed') {
                                $set('processed_by_id', auth()->id());
                                $set('processed_at', now());
                            }
                        })
                        ->required(),
                    Hidden::make('processed_by_id')
                        ->default(function () use ($record) {
                            return $record->processed_by_id ?? auth()->id();
                        }),
                    Hidden::make('processed_at')
                        ->default(function () use ($record) {
                            return $record->processed_at ?? now();
                        }),
                ])
                ->columns(2),
        ];
    }
    
    /**
     * Personaliza la data antes de guardar para asegurar que los campos de procesamiento se guarden correctamente
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Asegurarse que se actualiza correctamente el procesado cuando cambia a 'processed'
        if ($data['status'] === 'processed' && $this->record->status !== 'processed') {
            $data['processed_by_id'] = auth()->id();
            $data['processed_at'] = now();
        }
        
        return $data;
    }

    /**
     * Override the default notification with our custom notification
     */
    protected function afterSave(): void
    {
        // If the status is 'processed', send notification to the operator
        if ($this->record->status === 'processed') {
            $requestedBy = $this->record->requestedBy;
            if ($requestedBy) {
                // Send notification to the operator who requested the email
                $notification = Notification::make()
                    ->title('✅ Email procesado')
                    ->body('Tu solicitud de email para ' . $this->record->company->name . ' ha sido procesada.')
                    ->icon('heroicon-o-envelope')
                    ->iconColor('success')
                    ->actions([
                        NotificationAction::make('view')
                            ->label('Ver detalles')
                            ->url(route('filament.dashboard.resources.email-requests.view', ['record' => $this->record->id]))
                            ->button()
                    ]);
                    
                // Send to operator's database notifications
                $notification->sendToDatabase($requestedBy);
                
                // Also send to session for immediate visibility
                $notification->send();
            }
        }
    }
    
    protected function getSavedNotification(): ?Notification
    {
        // Send default notification to the admin
        return Notification::make()
            ->success()
            ->title('Solicitud actualizada')
            ->body('La solicitud de email ha sido actualizada correctamente.');
    }
}
