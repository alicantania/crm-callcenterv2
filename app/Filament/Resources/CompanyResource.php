<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre de la empresa')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('cif')
                ->label('CIF')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('address')
                ->label('Dirección')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('city')
                ->label('Ciudad')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('province')
                ->label('Provincia')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('phone')
                ->label('Teléfono')
                ->nullable()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->nullable()
                ->maxLength(255)
                ->email(),

            Forms\Components\TextInput::make('activity')
                ->label('Actividad')
                ->nullable()
                ->maxLength(255),

            Forms\Components\TextInput::make('cnae')
                ->label('CNAE')
                ->nullable()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre de la empresa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cif')
                    ->label('CIF')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('address')
                    ->label('Dirección')
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('Ciudad')
                    ->sortable(),

                Tables\Columns\TextColumn::make('province')
                    ->label('Provincia')
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                    

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('activity')
                    ->label('Actividad')
                    ->searchable(),

                Tables\Columns\TextColumn::make('cnae')
                    ->label('CNAE')
                    ->searchable(),
        ])
        ->filters([
            // Aquí podemos agregar filtros si lo necesitamos
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            // Aquí agregamos las acciones en masa, si es necesario
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
