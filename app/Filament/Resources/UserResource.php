<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\SelectColumn;






use App\Helpers\RoleHelper;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuarios';
    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';
    protected static ?string $navigationGroup = 'Gerencia';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('identification_number')
                    ->label('DNI')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(9),

                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required(),

                Forms\Components\TextInput::make('last_name')
                    ->label('Apellido 1')
                    ->required(),

                Forms\Components\TextInput::make('middle_name')
                    ->label('Apellido 2'),

                Forms\Components\TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->required(fn (string $context) => $context === 'create')
                    ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state)),

                Select::make('role_id')
                    ->label('Rol')
                    ->relationship('role', 'name')
                    ->required(),

                CheckboxList::make('businessLines')
                    ->label('Líneas de negocio')
                    ->relationship('businessLines', 'name')
                    ->columns(2),

                Forms\Components\Toggle::make('active')
                    ->label('¿Está activo?'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                    
                TextColumn::make('email')
                    ->label('Correo')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                    
                TextColumn::make('role.name')
                    ->label('Rol')
                    ->sortable()
                    ->searchable(),
                    
                BadgeColumn::make('role.name')
                    ->label('Estado')
                    ->colors([
                        'primary' => 'Admin',
                        'success' => 'Gerencia',
                        'warning' => 'Operador',
                        'danger' => 'SuperAdmin',
                    ])
                    ->sortable(),
                    
                BooleanColumn::make('active')
                    ->label('Activo')
                    ->sortable()
                    ->toggleable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                TextColumn::make('last_login_at')
                    ->label('Último acceso')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->relationship('role', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Solo activos')
                    ->default(true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->deferLoading()
            ->persistFiltersInSession()
            ->paginated([10, 25, 50, 100]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return RoleHelper::userHasRole(['Gerencia']);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
