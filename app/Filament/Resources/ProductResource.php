<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $navigationGroup = 'Gerencia';
    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nombre del producto')
                    ->required()
                    ->maxLength(255),

                TextInput::make('description')
                    ->label('Descripción')
                    ->maxLength(1000),

                TextInput::make('price')
                    ->label('Precio del producto (€)')
                    ->numeric()
                    ->required(),

                TextInput::make('commission_percentage')
                    ->label('Porcentaje de comisión para operador (%)')
                    ->numeric()
                    ->required(),

                Select::make('business_line_id')
                    ->label('Línea de negocio')
                    ->relationship('businessLine', 'name')
                    ->required(),

                Toggle::make('available')
                    ->label('Disponible')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('price')->label('Precio (€)')->sortable(),
                TextColumn::make('commission_percentage')->label('Comisión (%)')->sortable(),
                TextColumn::make('businessLine.name')->label('Línea de negocio'),
                ToggleColumn::make('available')->label('Disponible'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
