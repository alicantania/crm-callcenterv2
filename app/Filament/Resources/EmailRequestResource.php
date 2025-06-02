<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailRequestResource\Pages;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\EmailRequestResource\RelationManagers;
use App\Models\EmailRequest;
use App\Helpers\RoleHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmailRequestResource\Pages\CreateEmailRequest;
use App\Filament\Resources\EmailRequestResource\Pages\EditEmailRequest;
use App\Filament\Resources\EmailRequestResource\Pages\ViewEmailRequest;

class EmailRequestResource extends Resource
{
    protected static ?string $model = EmailRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Solicitudes de Email';
    protected static ?string $modelLabel = 'Solicitud de Email';
    protected static ?string $pluralModelLabel = 'Solicitudes de Email';
    protected static ?string $navigationGroup = 'Comunicaciones';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (RoleHelper::userHasRole(['Operador'])) {
            // Mostrar badge solo de solicitudes procesadas y no vistas por este operador
            $count = static::$model::where('status', 'processed')
                ->where('requested_by_id', $user->id)
                ->where('operator_seen', false)
                ->count();
        } else {
            // Admin badge muestra solicitudes pendientes
            $count = static::$model::where('status', 'pending')->count();
        }
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la solicitud')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required()
                            ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\EmailRequestResource\Pages\EditEmailRequest)
                            ->searchable()
                            ->preload()
                            ->label('Empresa'),
                            
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required()
                            ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\EmailRequestResource\Pages\EditEmailRequest)
                            ->searchable()
                            ->preload()
                            ->label('Curso/Producto'),
                            
                        Forms\Components\TextInput::make('email_to')
                            ->label('Email de destino')
                            ->email()
                            ->required()
                            ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\EmailRequestResource\Pages\EditEmailRequest),
                            
                        Forms\Components\TextInput::make('contact_person')
                            ->label('Persona de contacto')
                            ->maxLength(255)
                            ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\EmailRequestResource\Pages\EditEmailRequest),
                            
                        Forms\Components\Select::make('requested_by_id')
                            ->relationship('requestedBy', 'name')
                            ->label('Solicitado por')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\EmailRequestResource\Pages\EditEmailRequest),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Notas')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas del operador')
                            ->helperText('Notas adicionales proporcionadas por el operador')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\EmailRequestResource\Pages\EditEmailRequest),
                            
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
                            ->visible(fn (Forms\Get $get) => $get('status') === 'processed')
                            ->disabled(),
                            
                        Forms\Components\DateTimePicker::make('processed_at')
                            ->label('Fecha de procesamiento')
                            ->visible(fn (Forms\Get $get) => $get('status') === 'processed')
                            ->default(now())
                            ->disabled(),
                    ])
                    ->columns(3),
            ]);
    }

    /**
     * Filtra las solicitudes para que los operadores solo vean las suyas
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Si es un operador, solo mostrar sus solicitudes
        if (auth()->user() && auth()->user()->role->name === 'Operador') {
            $query->where('requested_by_id', auth()->id());
        }
        
        return $query;
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable(),
                    
                // Curso/Producto column removed
                    
                Tables\Columns\TextColumn::make('email_to')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copiado al portapapeles')
                    ->copyMessageDuration(1500),
                    
                // Contact person column removed
                    
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
                    
                // Processed date column removed
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
                // Operator sees only the view details action
                Tables\Actions\Action::make('view')
                    ->visible(fn (EmailRequest $record): bool => auth()->user()?->hasRole('operator'))
                    ->label('Ver detalles')
                    ->icon('heroicon-o-eye')
                    ->url(fn (EmailRequest $record): string => route('filament.dashboard.resources.email-requests.view', $record)),
                // Admin, Gerencia y Superadmin can edit (process) requests
                Tables\Actions\EditAction::make()
                    ->visible(fn (EmailRequest $record): bool => ! auth()->user()?->hasRole('operator')),
            ])
            ->bulkActions([
                // Bulk actions removed
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
            'view' => Pages\ViewEmailRequest::route('/{record}/view'),
            'edit' => Pages\EditEmailRequest::route('/{record}/edit'),
        ];
    }
    
    /**
     * Determine if the current user can edit the given resource.
     */
    public static function canEdit(Model $record): bool
    {
        // Only admin, gerencia and superadmin can edit (not operators)
        return RoleHelper::userHasNotRole(['Operador']);
    }
    
    /**
     * Determine if the current user can view the given resource.
     */
    public static function canView(Model $record): bool
    {
        // All users can view (mainly for operators)
        return true;
    }
}
