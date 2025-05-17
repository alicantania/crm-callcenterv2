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






class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuarios';
    protected static ?string $modelLabel = 'Usuario';
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
        ->columns([
            TextColumn::make('name')
                ->label('Nombre')
                ->sortable()
                ->searchable(),
                
            TextColumn::make('email')
                ->label('Correo Electrónico')
                ->sortable()
                ->searchable(),
                
            TextColumn::make('role.name')
                ->label('Rol')
                ->sortable()
                ->searchable(),
                
                BooleanColumn::make('active')
                ->label('Estado')
                ->sortable()
                ->toggleable()  // Usamos toggle() para permitir cambiar el valor de "activo" directamente
                ->trueIcon('heroicon-o-check-circle') // Icono para cuando es activo
                ->falseIcon('heroicon-o-x-circle') // Icono para cuando es inactivo
                
        ])
        ->filters([
            // Puedes agregar filtros aquí si lo deseas
        ])
        ->actions([
            // Puedes definir acciones personalizadas, por ejemplo: Editar, Eliminar
        ])
        ->bulkActions([
            // Puedes agregar acciones en masa aquí
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
