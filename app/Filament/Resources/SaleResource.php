<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form as FilamentForm;
use Filament\Resources\Resource;
use Filament\Tables;  
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use App\Models\Product;



class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Ventas';

    public static function form(FilamentForm $form): FilamentForm
    {
        return $form
            ->schema([
                Section::make('📦 Datos de la Empresa')
                    ->schema([
                        TextInput::make('company_name')->label('Empresa')->required(),
                        TextInput::make('company_cif')->label('CIF')->required(),
                        TextInput::make('company_address')->label('Dirección')->required(),
                        TextInput::make('company_city')->label('Ciudad')->required(),
                        TextInput::make('company_province')->label('Provincia')->required(),
                        TextInput::make('company_phone')->label('Teléfono'),
                        TextInput::make('company_mobile')->label('Móvil'),
                        TextInput::make('company_email')->label('Email')->email(),
                        TextInput::make('company_activity')->label('Actividad'),
                        TextInput::make('company_cnae')->label('CNAE'),
                        TextInput::make('contact_person')->label('Persona contacto'),
                        TextInput::make('social_security')->label('SS empresa'),
                        TextInput::make('iban')->label('IBAN'),
                    ])
                    ->columns(3),

                Section::make('🧑 Gestoría')
                    ->schema([
                        TextInput::make('gestoria_name')->label('Gestoría'),
                        TextInput::make('gestoria_cif')->label('CIF gestoría'),
                        TextInput::make('gestoria_phone')->label('Tel gestoría'),
                        TextInput::make('gestoria_email')->label('Email gestoría')->email(),
                    ])
                    ->columns(3),

                Section::make('👤 Representante Legal')
                    ->schema([
                        TextInput::make('legal_representative_name')->label('Nombre rep. legal'),
                        TextInput::make('legal_representative_dni')->label('DNI rep.'),
                        TextInput::make('representative_phone')->label('Tel rep.'),
                    ])
                    ->columns(3),

                Section::make('🎓 Alumno')
                    ->schema([
                        TextInput::make('student_name')->label('Nombre alumno'),
                        TextInput::make('student_dni')->label('DNI alumno'),
                        TextInput::make('student_social_security')->label('SS alumno'),
                        TextInput::make('student_phone')->label('Tel alumno'),
                        TextInput::make('student_email')->label('Email alumno')->email(),
                    ])
                    ->columns(3),

                    Section::make('Detalles de la Venta')
                    ->schema([
                        Select::make('product_id')
                            ->label('Curso')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                $product = Product::find($state);
                                $set('price', $product?->price ?? 0);
                                $set('commission_amount', $product
                                    ? round($product->price * ($product->commission_percentage / 100), 2)
                                    : 0
                                );
                            }),

                        TextInput::make('price')
                            ->label('Precio (€)')
                            ->numeric()
                            ->required()
                            ->disabled(),

                        TextInput::make('commission_amount')
                            ->label('Comisión (€)')
                            ->numeric()
                            ->required()
                            ->disabled(),

                        DatePicker::make('sale_date')
                            ->label('Fecha venta')
                            ->default(now())
                            ->required(),

                        Select::make('operator_id')
                            ->label('Operador que hizo la venta')
                            ->relationship('operator', 'name')
                            ->default(fn () => auth()->id())
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')->label('Empresa'),
                Tables\Columns\TextColumn::make('product.name')->label('Curso'),
                Tables\Columns\TextColumn::make('sale_price')->label('Precio'),
                Tables\Columns\TextColumn::make('commission_amount')->label('Comisión'),
                Tables\Columns\TextColumn::make('sale_date')->date()->label('Fecha'),
                Tables\Columns\TextColumn::make('status')->label('Estado'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
