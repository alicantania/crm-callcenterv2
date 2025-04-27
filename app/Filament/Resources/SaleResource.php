<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('📦 Datos de la Empresa')
                    ->schema([
                        TextInput::make('company_cif')->label('CIF')->default(fn () => request()->get('empresa_cif'))->required(),
                        TextInput::make('company_name')->label('Nombre de la empresa')->default(fn () => request()->get('empresa_name'))->required(),
                        TextInput::make('company_address')->label('Dirección')->default(fn () => request()->get('empresa_address'))->required(),
                        TextInput::make('company_city')->label('Localidad')->default(fn () => request()->get('empresa_city'))->required(),
                        TextInput::make('company_province')->label('Provincia')->default(fn () => request()->get('empresa_province'))->required(),
                        TextInput::make('company_phone')->label('Teléfono')->default(fn () => request()->get('empresa_phone')),
                        TextInput::make('company_mobile')->label('Móvil')->default(fn () => request()->get('empresa_mobile')),
                        TextInput::make('company_email')->label('Email')->default(fn () => request()->get('empresa_email'))->email(),
                        TextInput::make('company_activity')->label('Actividad')->default(fn () => request()->get('empresa_activity')),
                        TextInput::make('company_cnae')->label('CNAE')->default(fn () => request()->get('empresa_cnae')),
                        TextInput::make('contact_person')->label('Persona de contacto')->default(fn () => request()->get('empresa_contact_person')),
                        TextInput::make('iban')->label('IBAN')->default(fn () => request()->get('empresa_iban')),
                        TextInput::make('social_security')->label('Seguridad Social de la empresa')->default(fn () => request()->get('empresa_social_security')),
                    ])
                    ->columns(3),

                Section::make('🧑 Gestoría')
                    ->schema([
                        TextInput::make('gestoria_name')->label('Nombre de la gestoría')->default(fn () => request()->get('gestoria_name')),
                        TextInput::make('gestoria_email')->label('Email de la gestoría')->default(fn () => request()->get('gestoria_email'))->email(),
                        TextInput::make('gestoria_phone')->label('Teléfono de la gestoría')->default(fn () => request()->get('gestoria_phone')),
                    ])
                 ->columns(3),



                Section::make('🧑 Representante Legal')
                    ->schema([
                        TextInput::make('legal_representative_name')
                            ->label('Nombre del representante legal'),

                        TextInput::make('legal_representative_dni')
                            ->label('DNI del representante'),

                        TextInput::make('representative_phone')->label('Teléfono del representante')->default(fn () => request()->get('representative_phone')),
                    ])
                    ->columns(3),

                Section::make('🎓 Alumno')
                    ->schema([
                        TextInput::make('student_name')
                            ->label('Nombre del alumno'),

                        TextInput::make('student_dni')
                            ->label('DNI del alumno'),

                        TextInput::make('student_social_security')
                            ->label('Seguridad social del alumno'),

                        TextInput::make('student_phone')
                            ->label('Teléfono del alumno'),

                        TextInput::make('student_email')
                            ->label('Email del alumno')
                            ->email(),
                    ])
                    ->columns(3),

                    Section::make('📄 Detalles de la Venta')
                    ->schema([
                        Select::make('product_id')
                            ->label('Producto vendido')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->required(),

                        DatePicker::make('sale_date')
                            ->label('Fecha de venta')
                            ->default(now())
                            ->required(),

                        Select::make('operator_id')
                            ->label('Operador que hizo la venta')
                            ->relationship('operator', 'name')
                            ->default(auth()->id())
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
