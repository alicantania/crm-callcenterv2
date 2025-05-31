<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailRequestResource\Pages;
use App\Filament\Resources\EmailRequestResource\RelationManagers;
use App\Models\EmailRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmailRequestResource extends Resource
{
    protected static ?string $model = EmailRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Solicitudes de Email';
    protected static ?string $modelLabel = 'Solicitud de Email';
    protected static ?string $pluralModelLabel = 'Solicitudes de Email';
    protected static ?string $navigationGroup = 'Comunicaciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la solicitud')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Empresa'),
                            
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Curso/Producto'),
                            
                        Forms\Components\TextInput::make('email_to')
                            ->label('Email de destino')
                            ->email()
                            ->required(),
                            
                        Forms\Components\TextInput::make('contact_person')
                            ->label('Persona de contacto')
                            ->maxLength(255),
                            
                        Forms\Components\Select::make('requested_by_id')
                            ->relationship('requestedBy', 'name')
                            ->label('Solicitado por')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Notas')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas del operador')
                            ->helperText('Notas adicionales proporcionadas por el operador')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notas del administrador')
                            ->helperText('Notas añadidas por el administrador durante el procesamiento')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
                    
                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'processed' => 'Procesado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required()
                            ->default('pending'),
                            
                        Forms\Components\Select::make('processed_by_id')
                            ->relationship('processedBy', 'name')
                            ->label('Procesado por')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('status') === 'processed'),
                            
                        Forms\Components\DateTimePicker::make('processed_at')
                            ->label('Fecha de procesamiento')
                            ->visible(fn (Forms\Get $get) => $get('status') === 'processed')
                            ->default(now()),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Curso/Producto')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('email_to')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copiado al portapapeles')
                    ->copyMessageDuration(1500),
                    
                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contacto')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Solicitado por')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'processed' => 'Procesado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de solicitud')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('processed_at')
                    ->label('Fecha de procesamiento')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'processed' => 'Procesado',
                        'cancelled' => 'Cancelado',
                    ]),
                Tables\Filters\SelectFilter::make('requested_by_id')
                    ->label('Solicitado por')
                    ->relationship('requestedBy', 'name'),
                Tables\Filters\Filter::make('created_at')
                    ->label('Fecha de solicitud')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('process')
                    ->label('Procesar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (EmailRequest $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notas del administrador')
                            ->helperText('Añade notas sobre el email enviado. Estas notas serán visibles para el operador.')
                            ->placeholder('Ej: Email enviado con información del curso. Cliente interesado en modalidad online.')
                            ->maxLength(65535)
                            ->required(),
                    ])
                    ->action(function (EmailRequest $record, array $data): void {
                        // Usar el método del modelo para marcar como procesado
                        $record->markAsProcessed(auth()->id(), $data['admin_notes']);
                        
                        // Notificar al operador que solicitó el email
                        $requestedBy = $record->requestedBy;
                        if ($requestedBy) {
                            \Filament\Notifications\Notification::make()
                                ->title('✅ Email procesado')
                                ->body('El administrador ha enviado el email solicitado para ' . $record->company->name)
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->label('Ver detalles')
                                        ->url(route('filament.admin.resources.email-requests.edit', $record))
                                ])
                                ->sendToDatabase($requestedBy);
                        }
                        
                        // Mostrar notificación toast
                        \Filament\Notifications\Notification::make()
                            ->title('✅ Solicitud procesada correctamente')
                            ->body('Se ha marcado como procesada la solicitud de email para ' . $record->company->name)
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (EmailRequest $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Motivo de cancelación')
                            ->helperText('Indica el motivo por el que se cancela esta solicitud')
                            ->placeholder('Ej: Datos incorrectos, solicitud duplicada, etc.')
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar solicitud de email')
                    ->modalDescription('¿Estás seguro de que deseas cancelar esta solicitud? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, cancelar solicitud')
                    ->action(function (EmailRequest $record, array $data): void {
                        // Usar el método del modelo para marcar como cancelado
                        $record->markAsCancelled(auth()->id(), $data['admin_notes']);
                        
                        // Notificar al operador que solicitó el email
                        $requestedBy = $record->requestedBy;
                        if ($requestedBy) {
                            \Filament\Notifications\Notification::make()
                                ->title('❌ Solicitud de email cancelada')
                                ->body('Tu solicitud de email para ' . $record->company->name . ' ha sido cancelada: ' . ($data['admin_notes'] ?? 'Sin motivo especificado'))
                                ->sendToDatabase($requestedBy);
                        }
                        
                        // Mostrar notificación toast
                        \Filament\Notifications\Notification::make()
                            ->title('Solicitud cancelada')
                            ->body('Se ha cancelado la solicitud de email')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailRequests::route('/'),
            'create' => Pages\CreateEmailRequest::route('/create'),
            'edit' => Pages\EditEmailRequest::route('/{record}/edit'),
        ];
    }
}
