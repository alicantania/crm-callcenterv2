<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CallResource\Pages;
use App\Filament\Resources\CallResource\RelationManagers;
use App\Models\Call;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;

use App\Helpers\RoleHelper;

class CallResource extends Resource
{
    protected static ?string $model = Call::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';
    protected static ?string $navigationLabel = 'Llamadas';
    protected static ?string $modelLabel = 'Llamada';
    protected static ?string $pluralModelLabel = 'Llamadas';
    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Operador')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\Placeholder::make('company_phone')
                    ->label('Teléfono de la empresa')
                    ->reactive()
                    ->content(fn (callable $get) => optional(\App\Models\Company::find($get('company_id')))->phone ?? '-')
                    ->columnSpanFull(),

                Forms\Components\DatePicker::make('call_date')
                    ->label('Fecha de llamada')
                    ->required(),

                Forms\Components\TimePicker::make('call_time')
                    ->label('Hora de llamada')
                    ->seconds(false)
                    ->required(),

                Forms\Components\TextInput::make('duration')
                    ->label('Duración (min)')
                    ->numeric()
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('call_date', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Operador')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                    
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                    
                TextColumn::make('company.phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                    
                TextColumn::make('call_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                TextColumn::make('call_time')
                    ->label('Hora')
                    ->formatStateUsing(fn($state, $record) => $state
                        ? substr($state, 0, 5)
                        : ($record->call_date
                            ? date('H:i', strtotime($record->call_date))
                            : '-'))
                    ->sortable(),
                    
                TextColumn::make('duration')
                    ->label('Duración (min)')
                    ->sortable()
                    ->alignEnd(),
                    
                TextColumn::make('notes')
                    ->label('Notas')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filtrar por operador'),
                    
                Tables\Filters\Filter::make('call_date')
                    ->form([
                        Forms\Components\DatePicker::make('call_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('call_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['call_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('call_date', '>=', $date),
                            )
                            ->when(
                                $data['call_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('call_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->deferLoading()
            ->persistFiltersInSession()
            ->paginated([10, 25, 50, 100]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return RoleHelper::userHasRole(['Administrador', 'Gerencia']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCalls::route('/'),
        ];
    }
}
