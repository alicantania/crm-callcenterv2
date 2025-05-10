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

        $defaults = [
            'operator_id' => Auth::id(),
            'sale_date' => now()->toDateString(),
        ];

        if ($empresaId = request()->get('empresa_id')) {
            $this->empresa = Company::find($empresaId);
            if ($this->empresa) {
                $empresaData = [
                    'company_id' => $this->empresa->id,
                    'company_name' => $this->empresa->name,
                    'cif' => $this->empresa->cif,
                    'address' => $this->empresa->address,
                    'city' => $this->empresa->city,
                    'province' => $this->empresa->province,
                    'phone' => $this->empresa->phone,
                    'email' => $this->empresa->email,
                    'activity' => $this->empresa->activity,
                    'cnae' => $this->empresa->cnae,
                    'contact_person' => $this->empresa->contact_person,
                    'company_iban' => $this->empresa->iban,
                    'ss_company' => $this->empresa->ss_company,
                    'gestoria_name' => $this->empresa->gestoria_name,
                    'gestoria_email' => $this->empresa->gestoria_email,
                    'gestoria_phone' => $this->empresa->gestoria_phone,
                    'legal_representative_phone' => $this->empresa->representative_phone,
                ];

                $defaults = array_merge($defaults, $empresaData);
            }
        }

        $this->form->fill($defaults);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['operator_id'] = Auth::id();

        // Asegura que business_line_id siempre tenga valor si el producto lo tiene
        if (empty($data['business_line_id']) && !empty($data['product_id'])) {
            $data['business_line_id'] = Product::find($data['product_id'])?->business_line_id;
        }

        return $data;
    }


    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('📦 Datos de la Empresa')
                ->columns(3)
                ->schema([
                    Forms\Components\Hidden::make('company_id'),

                    Forms\Components\TextInput::make('cif')->label('CIF')->required()->disabled(),
                    Forms\Components\TextInput::make('company_name')->label('Empresa')->required()->disabled(),
                    Forms\Components\TextInput::make('address')->label('Dirección')->required()->disabled(),
                    Forms\Components\TextInput::make('city')->label('Ciudad')->required()->disabled(),
                    Forms\Components\TextInput::make('province')->label('Provincia')->required()->disabled(),
                    Forms\Components\TextInput::make('phone')->label('Teléfono')->disabled(),
                    Forms\Components\TextInput::make('email')->label('Email')->email()->disabled(),
                    Forms\Components\TextInput::make('activity')->label('Actividad')->disabled(),
                    Forms\Components\TextInput::make('cnae')->label('CNAE')->disabled(),
                    Forms\Components\TextInput::make('contact_person')->label('Persona contacto')->disabled(),
                    Forms\Components\TextInput::make('company_iban')->label('IBAN')->disabled(),
                    Forms\Components\TextInput::make('ss_company')->label('SS Empresa')->disabled(),
                    Forms\Components\TextInput::make('gestoria_name')->label('Gestoría')->disabled(),
                    Forms\Components\TextInput::make('gestoria_email')->label('Email Gestoría')->disabled(),
                    Forms\Components\TextInput::make('gestoria_phone')->label('Tel. Gestoría')->disabled(),
                    Forms\Components\TextInput::make('legal_representative_phone')->label('Tel. Rep. Legal')->disabled(),
                ]),

            Forms\Components\Section::make('📄 Detalles de la Venta')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label('Producto')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $product = Product::find($state);
                            $set('sale_price', $product?->price ?? 0);
                            $set('commission_amount', $product
                                ? round($product->price * ($product->commission_percentage / 100), 2)
                                : 0
                            );
                            $set('business_line_id', $product?->business_line_id ?? null);
                        }),

                    Forms\Components\Hidden::make('business_line_id')
                        ->required()
                        ->dehydrated(true),

                    Forms\Components\TextInput::make('sale_price')
                        ->label('Precio (€)')
                        ->numeric()
                        ->required()
                        ->disabled()
                        ->dehydrated(true),

                    Forms\Components\TextInput::make('commission_amount')
                        ->label('Comisión (€)')
                        ->numeric()
                        ->required()
                        ->disabled()
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

    protected function getRedirectUrl(): string
    {
        return SaleResource::getUrl('index'); // Esto te lleva a /dashboard/sales
    }
}
