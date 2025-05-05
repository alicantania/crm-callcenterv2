<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Company;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    public ?Company $empresa = null;

    public function mount(): void
    {
        parent::mount();

        if ($empresaId = request()->get('empresa_id')) {
            $this->empresa = Company::find($empresaId);
            if ($this->empresa) {
                $this->form->fill([
                    'company_id' => $this->empresa->id,
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
                    'contact_person' => $this->empresa->contact_person,
                    'iban' => $this->empresa->iban,
                    'social_security' => $this->empresa->ss_company,
                ]);
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['operator_id'] = Auth::id();

        // company_id comes from hidden field
        return $data;
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('📦 Datos de la Empresa')
                ->columns(3)
                ->schema([
                    Forms\Components\Hidden::make('company_id'),
                    Forms\Components\TextInput::make('company_cif')->label('CIF')->required()->disabled(),
                    Forms\Components\TextInput::make('company_name')->label('Empresa')->required()->disabled(),
                    Forms\Components\TextInput::make('company_address')->label('Dirección')->required()->disabled(),
                    Forms\Components\TextInput::make('company_city')->label('Ciudad')->required()->disabled(),
                    Forms\Components\TextInput::make('company_province')->label('Provincia')->required()->disabled(),
                    Forms\Components\TextInput::make('company_postal_code')->label('Código postal')->required()->disabled(),
                    Forms\Components\TextInput::make('company_phone')->label('Teléfono')->disabled(),
                    Forms\Components\TextInput::make('company_email')->label('Email')->email()->disabled(),
                    Forms\Components\TextInput::make('company_activity')->label('Actividad')->disabled(),
                    Forms\Components\TextInput::make('company_cnae')->label('CNAE')->disabled(),
                    Forms\Components\TextInput::make('contact_person')->label('Contacto')->disabled(),
                    Forms\Components\TextInput::make('iban')->label('IBAN')->disabled(),
                    Forms\Components\TextInput::make('social_security')->label('SS Empresa')->disabled(),
                ]),

            Forms\Components\Section::make('📄 Detalles de la Venta')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label('Producto')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->required()
                        ->reactive(),

                    Forms\Components\TextInput::make('sale_price')
                        ->label('Precio (€)')
                        ->numeric()
                        ->default(fn (Get $get) => optional(Product::find($get('product_id')))->price ?? 0)
                        ->disabled()
                        ->required()
                        ->dehydrated(true),

                    Forms\Components\TextInput::make('commission_amount')
                        ->label('Comisión (€)')
                        ->numeric()
                        ->default(fn (Get $get) => (
                            $product = Product::find($get('product_id'))
                        ) ? round($product->price * ($product->commission_percentage / 100), 2) : 0)
                        ->disabled()
                        ->required()
                        ->dehydrated(true),

                    Forms\Components\DatePicker::make('sale_date')
                        ->label('Fecha de venta')
                        ->default(now())
                        ->required(),

                    Forms\Components\Select::make('operator_id')
                        ->label('Operador')
                        ->relationship('operator', 'name')
                        ->default(Auth::id())
                        ->required(),
                ]),
        ];
    }
}
