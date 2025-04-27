<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    public $empresaId = null;
    public ?Company $empresa = null;

    public function mount(): void
    {
        parent::mount();

        $this->empresaId = request()->get('empresa_id');

        if ($this->empresaId) {
            $this->empresa = Company::find($this->empresaId);

            if ($this->empresa) {
                $this->form->fill([
                    'company_name' => $this->empresa->name,
                    'company_cif' => $this->empresa->cif,
                    'company_address' => $this->empresa->address,
                    'company_city' => $this->empresa->city,
                    'company_province' => $this->empresa->province,
                    'company_postal_code' => $this->empresa->postal_code,
                    'company_phone' => $this->empresa->phone,
                    'company_email' => $this->empresa->email,
                    'company_activity' => $this->empresa->activity,
                    'company_cnae' => $this->empresa->cnae,
                ]);
            }
        }
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
                    Forms\Components\TextInput::make('company_name')->label('Nombre de la empresa')->required(),
                    Forms\Components\TextInput::make('company_cif')->label('CIF')->required(),
                    Forms\Components\TextInput::make('company_address')->label('Dirección')->required(),
                    Forms\Components\TextInput::make('company_city')->label('Ciudad')->required(),
                    Forms\Components\TextInput::make('company_province')->label('Provincia')->required(),
                    Forms\Components\TextInput::make('company_postal_code')->label('Código postal')->required(),
                    Forms\Components\TextInput::make('company_phone')->label('Teléfono')->required(),
                    Forms\Components\TextInput::make('company_email')->label('Email')->email()->required(),
                    Forms\Components\TextInput::make('company_activity')->label('Actividad')->required(),
                    Forms\Components\TextInput::make('company_cnae')->label('CNAE')->required(),
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

                    Forms\Components\TextInput::make('price')
                        ->label('Precio de venta (€)')
                        ->numeric()
                        ->nullable(), // ✅ Ahora no da error si está vacío

                    Forms\Components\TextInput::make('commission_amount')
                        ->label('Comisión (€)')
                        ->numeric()
                        ->nullable(), // ✅ Ahora no da error si está vacío

                    Forms\Components\DatePicker::make('sale_date')
                        ->label('Fecha de venta')
                        ->default(now())
                        ->required(),
                ])
                ->columns(2),
        ];
    }
}
