<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select as SelectComponent;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\RoleHelper;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon   = 'heroicon-o-cube';
    protected static ?string $navigationLabel  = 'Productos';
    protected static ?string $modelLabel       = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';
    protected static ?int    $navigationSort   = 40;

    // ----------------------------------------------------
    // 1) PERMISOS GLOBALES (create / edit / delete)
    // ----------------------------------------------------

    /**
     * Solo rol 3 (Gerencia) puede crear productos.
     */
    public static function canCreate(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return RoleHelper::userHasRole(['Gerencia']);
    }

    /**
     * Solo rol 3 (Gerencia) puede editar productos.
     */
    public static function canEdit(Model $record): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return RoleHelper::userHasRole(['Gerencia']);
    }

    /**
     * Solo rol 3 (Gerencia) puede eliminar productos.
     */
    public static function canDelete(Model $record): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return RoleHelper::userHasRole(['Gerencia']);
    }

    // ----------------------------------------------------
    // 2) DEFINICIÓN DEL FORMULARIO (Create / Edit)
    // ----------------------------------------------------
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nombre del producto')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Descripción')
                    ->maxLength(1000)
                    ->rows(5)
                    ->columnSpanFull(),

                TextInput::make('price')
                    ->label('Precio del producto (€)')
                    ->numeric()
                    ->required(),

                TextInput::make('commission_percentage')
                    ->label('Porcentaje de comisión para operador (%)')
                    ->numeric()
                    ->required(),

                SelectComponent::make('business_line_id')
                    ->label('Línea de negocio')
                    ->relationship('businessLine', 'name')
                    ->required(),

                // El campo “available” solo visible para rol 3
                Toggle::make('available')
                    ->label('Disponible')
                    ->default(true)
                    ->visible(fn () => RoleHelper::userHasRole(['Gerencia'])),
            ]);
    }

    // ----------------------------------------------------
    // 3) DEFINICIÓN DE LA TABLA / LISTADO
    // ----------------------------------------------------
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

                // Mostrar interruptor “Disponible” solo para rol 3
                ToggleColumn::make('available')
                    ->label('Disponible')
                    ->onColor('success')
                    ->offColor('danger')
                    ->visible(fn () => RoleHelper::userHasRole(['Gerencia'])),


                // Para rol 1 y 2, mostrar texto “Sí/No”
                TextColumn::make('available')
                    ->label('Disponible')
                    ->formatStateUsing(fn (bool $state) => $state ? 'Sí' : 'No')
                    ->visible(fn () => !RoleHelper::userHasRole(['Gerencia'])),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('businessLine')
                    ->relationship('businessLine', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('available')
                    ->label('Solo disponibles')
                    ->default(true),
            ])
            ->actions([
                ViewAction::make(),

                EditAction::make()
                    // Solo rol 3 ve el botón “Editar”
                    ->visible(fn () => RoleHelper::userHasRole(['Gerencia'])),

                DeleteAction::make()
                    // Solo rol 3 ve el botón “Eliminar”
                    ->visible(fn () => RoleHelper::userHasRole(['Gerencia'])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        // Solo rol 3 ve la acción masiva “Eliminar”
                        ->visible(fn () => RoleHelper::userHasRole(['Gerencia'])),
                ]),
            ])
            ->deferLoading()
            ->persistFiltersInSession()
            ->paginated([10, 25, 50, 100]);
    }

    // ----------------------------------------------------
    // 4) MENÚ DE NAVEGACIÓN
    // ----------------------------------------------------
    public static function shouldRegisterNavigation(): bool
    {
        // Mostrar “Productos” en el sidebar para roles 1, 2, 3 y 4.
        // Solo rol 3 podrá gestionarlo al completo; 1, 2 y 4 únicamente verán el listado.
        return RoleHelper::userHasRole(['Gerencia', 'SuperAdmin', 'Operador', 'Administracion']);
    }

    // ----------------------------------------------------
    // 5) RELACIONES (si las hubiera)
    // ----------------------------------------------------
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    // ----------------------------------------------------
    // 6) RUTAS INTERNAS (List / Create / Edit)
    // ----------------------------------------------------
    public static function getPages(): array
    {
        // Registrar siempre todas las rutas y usar canCreate/canEdit para control de acceso
        return [
            'index'  => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit'   => EditProduct::route('/{record}/edit'),
        ];
    }
}
