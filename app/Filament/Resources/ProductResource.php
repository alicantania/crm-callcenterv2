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

use App\Helpers\RoleHelper;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';
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
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                    
                TextColumn::make('price')
                    ->label('Precio (€)')
                    ->sortable()
                    ->money('EUR')
                    ->alignEnd(),
                    
                TextColumn::make('commission_percentage')
                    ->label('Comisión (%)')
                    ->sortable()
                    ->suffix('%')
                    ->alignEnd(),
                    
                TextColumn::make('businessLine.name')
                    ->label('Línea de negocio')
                    ->searchable()
                    ->sortable(),
                    
                ToggleColumn::make('available')
                    ->label('Disponible')
                    ->onColor('success')
                    ->offColor('danger'),
                    
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('businessLine')
                    ->relationship('businessLine', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\TernaryFilter::make('available')
                    ->label('Solo disponibles')
                    ->default(true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => auth()->user()->role_id === 2), // Solo visible para gerencia
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()->role_id === 2), // Solo visible para gerencia
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->role_id === 2), // Solo visible para gerencia
                ]),
            ])
            ->deferLoading()
            ->persistFiltersInSession()
            ->paginated([10, 25, 50, 100]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return RoleHelper::userHasRole(['Gerencia', 'Operador']);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        $pages = [
            'index' => Pages\ListProducts::route('/'),
        ];
        
        // Solo agregar las páginas de creación y edición para usuarios con rol de gerencia
        if (auth()->check() && auth()->user()->role_id === 2) {
            $pages['create'] = Pages\CreateProduct::route('/create');
            $pages['edit'] = Pages\EditProduct::route('/{record}/edit');
        }
        
        return $pages;
    }
}
