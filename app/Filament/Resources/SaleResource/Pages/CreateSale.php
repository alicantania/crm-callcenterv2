<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    public $empresaId = null;

    public function mount(): void
    {
        parent::mount();

        // Capturar el ID de empresa que viene por GET
        $this->empresaId = request()->get('empresa_id');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['operator_id'] = Auth::id();
        $data['company_id'] = $this->empresaId;

        return $data;
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('📦 Datos de la Empresa')
                ->schema([
                    Forms\Components\TextInput::make('company_name')
                        ->label('Nombre de la empresa')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('company_cif')
                        ->label('CIF')
                        ->required()
                        ->maxLength(50),

                    Forms\Components\TextInput::make('company_address')
                        ->label('Dirección')
                        ->required(),

                    Forms\Components\TextInput::make('company_city')
                        ->label('Ciudad')
                        ->required(),

                    Forms\Components\TextInput::make('company_province')
                        ->label('Provincia')
                        ->required(),

                    Forms\Components\TextInput::make('company_postal_code')
                        ->label('Código postal')
                        ->required(),

                    Forms\Components\TextInput::make('company_phone')
                        ->label('Teléfono')
                        ->required(),

                    Forms\Components\TextInput::make('company_email')
                        ->label('Email')
                        ->email()
                        ->required(),

                    Forms\Components\TextInput::make('company_activity')
                        ->label('Actividad')
                        ->required(),

                    Forms\Components\TextInput::make('company_cnae')
                        ->label('CNAE')
                        ->required(),
                ])
                ->columns(2),

            Forms\Components\Section::make('🛒 Datos de la Venta')
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label('Producto vendido')
                        ->relationship('product', 'name')
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('business_line_id')
                        ->label('Línea de negocio')
                        ->relationship('businessLine', 'name')
                        ->required()
                        ->searchable(),

                    Forms\Components\DatePicker::make('sale_date')
                        ->label('Fecha de venta')
                        ->default(now())
                        ->required(),
                ])
                ->columns(2),
        ];
    }
}
