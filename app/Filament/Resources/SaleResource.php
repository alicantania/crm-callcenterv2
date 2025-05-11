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
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;




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
                        TextInput::make('company_name')
                            ->label('Empresa')
                            ->required()
                            ->default(Request::get('empresa_name')),
                
                        TextInput::make('cif')
                            ->label('CIF')
                            ->required()
                            ->default(Request::get('empresa_cif')),
                
                        TextInput::make('address')
                            ->label('Dirección')
                            ->required()
                            ->default(Request::get('empresa_address')),
                
                        TextInput::make('city')
                            ->label('Ciudad')
                            ->required()
                            ->default(Request::get('empresa_city')),
                
                        TextInput::make('province')
                            ->label('Provincia')
                            ->required()
                            ->default(Request::get('empresa_province')),
                
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->default(Request::get('empresa_phone')),
                
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->default(Request::get('empresa_email')),
                
                        TextInput::make('activity')
                            ->label('Actividad')
                            ->default(Request::get('empresa_activity')),
                
                        TextInput::make('cnae')
                            ->label('CNAE')
                            ->default(Request::get('empresa_cnae')),
                
                        TextInput::make('contact_person')
                            ->label('Persona contacto')
                            ->default(Request::get('empresa_contact_person')),
                
                        TextInput::make('company_iban')
                            ->label('IBAN')
                            ->default(Request::get('empresa_iban')),
                
                        TextInput::make('ss_company')
                            ->label('SS Empresa')
                            ->default(Request::get('empresa_social_security')),
                    ])
                    ->columns(3),

                Section::make('🧑 Gestoría')
                    ->schema([
                        TextInput::make('gestoria_name')
                            ->label('Gestoría')
                            ->default(Request::get('gestoria_name')),
                
                        TextInput::make('gestoria_cif')
                            ->label('CIF Gestoría'),
                
                        TextInput::make('gestoria_phone')
                            ->label('Tel Gestoría')
                            ->default(Request::get('gestoria_phone')),
                
                        TextInput::make('gestoria_email')
                            ->label('Email Gestoría')
                            ->email()
                            ->default(Request::get('gestoria_email')),
                    ])
                    ->columns(3),

                Section::make('👤 Representante Legal')
                    ->schema([
                        TextInput::make('legal_representative_name')
                            ->label('Nombre rep. legal'),
                
                        TextInput::make('legal_representative_dni')
                            ->label('DNI rep.'),
                
                        TextInput::make('legal_representative_phone')
                            ->label('Tel rep.')
                            ->default(Request::get('representative_phone')),
                    ])
                    ->columns(3),
                    

                Section::make('🎓 Alumno')
                    ->schema([
                        TextInput::make('student_name')->label('Nombre alumno'),
                        TextInput::make('student_dni')->label('DNI alumno'),
                        TextInput::make('student_ss')->label('SS alumno'),
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
                                $set('sale_price', $product?->price ?? 0);
                                $set('commission_amount', $product
                                    ? round($product->price * ($product->commission_percentage / 100), 2)
                                    : 0
                                );
                                $set('business_line_id', $product?->business_line_id ?? null);
                            }),

                        TextInput::make('sale_price')
                            ->label('Precio (€)')
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->default(fn (?Sale $record) => $record?->sale_price),

                        Select::make('business_line_id')
                            ->label('Línea de negocio')
                            ->relationship('businessLine', 'name')
                            ->required()
                            ->disabled() // 👈 evita que el usuario lo modifique manualmente
                            ->reactive(),

                        TextInput::make('commission_amount')
                            ->label('Comisión (€)')
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->default(fn (?Sale $record) => $record?->commission_amount),

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

            ->query(function () {
                $query = Sale::query();

                // Solo filtra por operador si NO eres Superadmin (role_id = 4)
                if (Auth::user()->role_id === 1) {
                    $query->where('operator_id', Auth::id());
                }

                return $query;
            })


            ->columns([
                Tables\Columns\TextColumn::make('company_name')->label('Empresa'),
                Tables\Columns\TextColumn::make('product.name')->label('Curso'),
                Tables\Columns\TextColumn::make('sale_price')->label('Precio'),
                Tables\Columns\TextColumn::make('commission_amount')->label('Comisión'),
                Tables\Columns\TextColumn::make('sale_date')->date()->label('Fecha'),
                Tables\Columns\TextColumn::make('status')
                ->label('Estado')
                ->badge()
                ->colors([
                    'gray' => 'pendiente',
                    'info' => 'tramitada',
                    'warning' => 'incidentada',
                    'danger' => 'anulada',
                    'success' => 'liquidada',
                ]),
            ])
            ->filters([])
            ->defaultSort('sale_date', 'desc') // ← ORDENA POR FECHA MÁS RECIENTE PRIMERO
            ->actions([
                Tables\Actions\EditAction::make()
                ->visible(false),
                Tables\Actions\Action::make('corregir')
                    ->label('Corregir venta')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning')
                    ->visible(fn ($record) =>
                        $record->status === 'devuelta' && $record->operator_id === auth()->id()
                    )
                    ->url(fn ($record) => SaleResource::getUrl('create', ['venta_id' => $record->id]))
                    //->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
            
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()?->role_id === 1) {
            return auth()->user()->sales()->where('status', 'devuelta')->count() ?: null;
        }

        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger'; // rojo si hay devueltas
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
