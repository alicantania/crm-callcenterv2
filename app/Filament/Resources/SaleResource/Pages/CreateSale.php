<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('📦 Datos generales')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('business_line_id')
                            ->relationship('businessLine', 'name')
                            ->required(),
                        Forms\Components\DatePicker::make('sale_date')
                            ->label('Fecha de venta')
                            ->required()
                            ->default(now()),
                        Forms\Components\Hidden::make('operator_id')
                            ->default(Auth::id()),
                    ])->columns(2),

                Forms\Components\Section::make('🧑 Representante legal')
                    ->schema([
                        Forms\Components\TextInput::make('legal_representative_name')->required(),
                        Forms\Components\TextInput::make('legal_representative_dni')->required(),
                        Forms\Components\TextInput::make('legal_representative_social_security')->required(),
                    ])->columns(3),

                Forms\Components\Section::make('🎓 Alumno')
                    ->schema([
                        Forms\Components\TextInput::make('student_name')->required(),
                        Forms\Components\TextInput::make('student_dni')->required(),
                        Forms\Components\TextInput::make('student_social_security')->required(),
                        Forms\Components\TextInput::make('student_phone'),
                        Forms\Components\TextInput::make('student_email'),
                    ])->columns(3),

                Forms\Components\Section::make('🗂️ Datos administrativos')
                    ->schema([
                        Forms\Components\Select::make('tramitator_id')
                            ->label('Tramitador')
                            ->relationship('tramitator', 'name')
                            ->searchable(),
                        Forms\Components\DatePicker::make('tramitated_at')
                            ->label('Fecha de tramitación'),
                        Forms\Components\TextInput::make('contract_number'),
                        Forms\Components\TextInput::make('commission'),
                        Forms\Components\Select::make('liquidator_id')
                            ->label('Liquidador')
                            ->relationship('liquidator', 'name')
                            ->searchable(),
                        Forms\Components\DatePicker::make('liquidated_at')
                            ->label('Fecha de liquidación'),
                    ])->columns(3),
            ]);
    }
}
