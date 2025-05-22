<?php

namespace App\Filament\Pages\Operator;

use App\Models\Call;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Livewire\Attributes\On; // <<< AÑADIDO ESTO

class LlamadaManualPage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-phone';
    protected static ?string $navigationLabel = 'Llamada Manual';
    protected static string $view = 'filament.pages.operator.llamada-manual';

    public ?Company $empresa = null;
    public array $formData = [];

    public ?string $resultado = null;
    public ?string $fecha_rellamada = null;
    public ?string $comentarios = null;
    public ?string $contacto = null;
    public ?string $motivo_desinteres = null;

    public function mount(): void
    {
        // Usar la sesión para mantener la empresa fija
        $empresaId = session('llamada_manual_empresa_id');
        if ($empresaId) {
            $this->empresa = Company::find($empresaId);
        } else {
            $this->empresa = Company::query()
                ->where(function ($query) {
                    $query->whereNull('assigned_operator_id')
                          ->orWhere('assigned_operator_id', Auth::id());
                })
                ->inRandomOrder()
                ->first();

            if ($this->empresa && $this->empresa->assigned_operator_id === null) {
                $this->empresa->updateQuietly(['assigned_operator_id' => Auth::id()]);
            }
            if ($this->empresa) {
                session(['llamada_manual_empresa_id' => $this->empresa->id]);
            }
        }
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('LlamadaManualTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Empresa')
                            ->schema([
                                Forms\Components\Section::make('Datos de la Empresa')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('empresa_nombre')
                                            ->label('Nombre empresa')
                                            ->default(fn() => $this->empresa?->name)
                                            ->required(),
                                        Forms\Components\TextInput::make('empresa_cif')
                                            ->label('CIF')
                                            ->default(fn() => $this->empresa?->cif)
                                            ->required(),
                                        Forms\Components\TextInput::make('empresa_address')
                                            ->label('Dirección')
                                            ->default(fn() => $this->empresa?->address),
                                        Forms\Components\TextInput::make('empresa_city')
                                            ->label('Ciudad')
                                            ->default(fn() => $this->empresa?->city),
                                        Forms\Components\TextInput::make('empresa_province')
                                            ->label('Provincia')
                                            ->default(fn() => $this->empresa?->province),
                                        Forms\Components\TextInput::make('empresa_phone')
                                            ->label('Teléfono')
                                            ->default(fn() => $this->empresa?->phone),
                                        Forms\Components\TextInput::make('empresa_email')
                                            ->label('Email')
                                            ->default(fn() => $this->empresa?->email),
                                        Forms\Components\TextInput::make('empresa_activity')
                                            ->label('Actividad')
                                            ->default(fn() => $this->empresa?->activity),
                                        Forms\Components\TextInput::make('empresa_cnae')
                                            ->label('CNAE')
                                            ->default(fn() => $this->empresa?->cnae),
                                        Forms\Components\TextInput::make('empresa_contact_person')
                                            ->label('Persona contacto')
                                            ->default(fn() => $this->empresa?->contact_person),
                                        Forms\Components\TextInput::make('empresa_iban')
                                            ->label('IBAN')
                                            ->default(fn() => $this->empresa?->iban),
                                        Forms\Components\TextInput::make('empresa_ss_company')
                                            ->label('SS Empresa')
                                            ->default(fn() => $this->empresa?->ss_company),
                                    ]),
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('guardar_empresa')
                                        ->label('Guardar datos de empresa')
                                        ->action('guardarEmpresa')
                                        ->color('primary')
                                        ->icon('heroicon-o-building-office')
                                ])
                            ]),
                        Forms\Components\Tabs\Tab::make('Representante Legal')
                            ->schema([
                                Forms\Components\Section::make('Representante Legal')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('rep_legal_nombre')
                                            ->label('Nombre representante')
                                            ->default(fn() => $this->empresa?->legal_representative_name),
                                        Forms\Components\TextInput::make('rep_legal_dni')
                                            ->label('DNI representante')
                                            ->default(fn() => $this->empresa?->legal_representative_dni),
                                        Forms\Components\TextInput::make('rep_legal_telefono')
                                            ->label('Teléfono representante')
                                            ->default(fn() => $this->empresa?->representative_phone),
                                    ]),
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('guardar_representante')
                                        ->label('Guardar representante')
                                        ->action('guardarRepresentante')
                                        ->color('primary')
                                        ->icon('heroicon-o-user')
                                ])
                            ]),
                        Forms\Components\Tabs\Tab::make('Gestoría')
                            ->schema([
                                Forms\Components\Section::make('Gestoría')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('gestoria_nombre')
                                            ->label('Nombre gestoría')
                                            ->default(fn() => $this->empresa?->gestoria_name),
                                        Forms\Components\TextInput::make('gestoria_cif')
                                            ->label('CIF gestoría')
                                            ->default(fn() => $this->empresa?->gestoria_cif),
                                        Forms\Components\TextInput::make('gestoria_telefono')
                                            ->label('Teléfono gestoría')
                                            ->default(fn() => $this->empresa?->gestoria_phone),
                                        Forms\Components\TextInput::make('gestoria_email')
                                            ->label('Email gestoría')
                                            ->default(fn() => $this->empresa?->gestoria_email),
                                    ]),
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('guardar_gestoria')
                                        ->label('Guardar gestoría')
                                        ->action('guardarGestoria')
                                        ->color('primary')
                                        ->icon('heroicon-o-briefcase')
                                ])
                            ]),
                        Forms\Components\Tabs\Tab::make('Historial de Llamadas')
                            ->schema([
                                Forms\Components\Section::make('Historial de llamadas a la empresa')
                                    ->description('Aquí puedes ver el historial de llamadas previas a esta empresa.')
                                    ->schema([
                                        Forms\Components\Placeholder::make('historial_llamadas')
                                            ->content(fn() => 'Aquí irá el historial de llamadas (solo lectura).')
                                    ])
                            ]),
                        Forms\Components\Tabs\Tab::make('Documentos')
                            ->schema([
                                Forms\Components\Section::make('Documentos de la empresa')
                                    ->description('Aquí puedes adjuntar y ver documentos relacionados con la empresa.')
                                    ->schema([
                                        Forms\Components\Placeholder::make('documentos')
                                            ->content('Funcionalidad de documentos pendiente de implementar.')
                                    ])
                            ]),
                        Forms\Components\Tabs\Tab::make('Notas internas')
                            ->schema([
                                Forms\Components\Section::make('Notas internas del operador')
                                    ->description('Solo visibles para operadores y administración.')
                                    ->schema([
                                        Forms\Components\Textarea::make('nota_interna')
                                            ->label('Nota interna')
                                            ->rows(4),
                                    ]),
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('guardar_nota')
                                        ->label('Guardar nota interna')
                                        ->action('guardarNotaInterna')
                                        ->color('primary')
                                        ->icon('heroicon-o-pencil')
                                ])
                            ]),
                        Forms\Components\Tabs\Tab::make('Curso Interesado')
                            ->schema([
                                Forms\Components\Section::make('Información de interés del cliente')
                                    ->description('Consulta y edita lo que se habló con el cliente en la última llamada.')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Select::make('curso_interesado')
                                            ->label('Curso')
                                            ->options(function () {
                                                return \App\Models\Product::with('businessLine')->get()->mapWithKeys(function ($product) {
                                                    $linea = $product->businessLine ? ' (' . $product->businessLine->name . ')' : '';
                                                    return [$product->id => $product->name . $linea];
                                                })->toArray();
                                            })
                                            ->searchable()
                                            ->required()
                                            ->reactive(),
                                        Forms\Components\TextInput::make('linea_negocio_interesada')
                                            ->label('Línea de negocio')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->default(function ($get) {
                                                $cursoId = $get('curso_interesado');
                                                if ($cursoId) {
                                                    $product = \App\Models\Product::with('businessLine')->find($cursoId);
                                                    return $product && $product->businessLine ? $product->businessLine->name : '';
                                                }
                                                return '';
                                            }),
                                        Forms\Components\TextInput::make('precio_interesado')
                                            ->label('Precio (€)')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->default(function ($get) {
                                                $cursoId = $get('curso_interesado');
                                                if ($cursoId) {
                                                    $product = \App\Models\Product::find($cursoId);
                                                    return $product ? $product->price : '';
                                                }
                                                return '';
                                            }),
                                        Forms\Components\TextInput::make('comision_interesada')
                                            ->label('Comisión (€)')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->default(function ($get) {
                                                $cursoId = $get('curso_interesado');
                                                if ($cursoId) {
                                                    $product = \App\Models\Product::find($cursoId);
                                                    return $product ? $product->commission_percentage : '';
                                                }
                                                return '';
                                            }),
                                        Forms\Components\Select::make('modalidad_interesada')
                                            ->label('Modalidad interesada')
                                            ->options([
                                                'online' => 'Online',
                                                'presencial' => 'Presencial',
                                                'mixto' => 'Mixto',
                                            ])
                                            ->default(fn() => $this->empresa?->modalidad_interesada),
                                        Forms\Components\DatePicker::make('fecha_interes')
                                            ->label('Fecha de interés')
                                            ->default(fn() => $this->empresa?->fecha_interes),
                                        Forms\Components\Textarea::make('observaciones_interes')
                                            ->label('Observaciones de interés')
                                            ->rows(3)
                                            ->default(fn() => $this->empresa?->observaciones_interes),
                                    ]),
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('guardar_curso_interesado')
                                        ->label('Guardar interés de cliente')
                                        ->action('guardarCursoInteresado')
                                        ->color('primary')
                                        ->icon('heroicon-o-academic-cap')
                                ])
                            ]),
                        Forms\Components\Tabs\Tab::make('Resultado de la llamada')
                            ->schema([
                                Forms\Components\Section::make("📞 Resultado de la llamada")
                                    ->description("Completa la información con cuidado para registrar correctamente el resultado de la llamada.")
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\Select::make('resultado')
                                                ->label('Resultado')
                                                ->options([
                                                    'no_interesa' => 'No interesa',
                                                    'no_contesta' => 'No contesta',
                                                    'volver_a_llamar' => 'Volver a llamar',
                                                    'contacto' => 'Contacto',
                                                    'error' => 'Error',
                                                ])
                                                ->reactive()
                                                ->required()
                                                ->columnSpanFull(),
                                            Forms\Components\TextInput::make('motivo_desinteres')
                                                ->label('Motivo del desinterés')
                                                ->placeholder('Ej: No tiene créditos, No quiere hacer cursos...')
                                                ->visible(fn (callable $get) => $get('resultado') === 'no_interesa')
                                                ->required(fn (callable $get) => $get('resultado') === 'no_interesa')
                                                ->columnSpanFull(),
                                        ]),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\DateTimePicker::make('fecha_rellamada')
                                                ->label('📅 ¿Cuándo volver a llamar?')
                                                ->minutesStep(5)
                                                ->withoutSeconds()
                                                ->displayFormat('d/m/Y H:i')
                                                ->native(false)
                                                ->visible(fn (callable $get) => in_array($get('resultado'), ['volver_a_llamar', 'contacto'])),
                                            Forms\Components\TextInput::make('contacto')
                                                ->label('👤 Persona de contacto')
                                                ->placeholder('Nombre de quien atiende...'),
                                        ]),
                                        Forms\Components\Textarea::make('comentarios')
                                            ->label('📝 Comentarios adicionales')
                                            ->autosize()
                                            ->rows(4)
                                            ->placeholder('Observaciones sobre la llamada...')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1)
                                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                            ])
                    ])
            ])
            ->statePath('formData')
            ->model(Call::class);
    }

    public function guardarEmpresa()
    {
        if (! $this->empresa) {
            Notification::make()
                ->title('❌ No hay empresa asignada')
                ->danger()
                ->send();
            return;
        }
        $data = $this->form->getRawState();
        $this->empresa->update([
            'name' => $data['empresa_nombre'] ?? $this->empresa->name,
            'cif' => $data['empresa_cif'] ?? $this->empresa->cif,
            'address' => $data['empresa_address'] ?? $this->empresa->address,
            'city' => $data['empresa_city'] ?? $this->empresa->city,
            'province' => $data['empresa_province'] ?? $this->empresa->province,
            'phone' => $data['empresa_phone'] ?? $this->empresa->phone,
            'email' => $data['empresa_email'] ?? $this->empresa->email,
            'activity' => $data['empresa_activity'] ?? $this->empresa->activity,
            'cnae' => $data['empresa_cnae'] ?? $this->empresa->cnae,
            'contact_person' => $data['empresa_contact_person'] ?? $this->empresa->contact_person,
            'iban' => $data['empresa_iban'] ?? $this->empresa->iban,
            'ss_company' => $data['empresa_ss_company'] ?? $this->empresa->ss_company,
        ]);
        Notification::make()
            ->title('✅ Datos de empresa actualizados')
            ->success()
            ->send();
    }

    public function guardarRepresentante()
    {
        if (! $this->empresa) {
            Notification::make()
                ->title('❌ No hay empresa asignada')
                ->danger()
                ->send();
            return;
        }
        $data = $this->form->getRawState();
        $this->empresa->update([
            'legal_representative_name' => $data['rep_legal_nombre'] ?? $this->empresa->legal_representative_name,
            'legal_representative_dni' => $data['rep_legal_dni'] ?? $this->empresa->legal_representative_dni,
            'representative_phone' => $data['rep_legal_telefono'] ?? $this->empresa->representative_phone,
        ]);
        Notification::make()
            ->title('✅ Representante legal actualizado')
            ->success()
            ->send();
    }

    public function guardarGestoria()
    {
        if (! $this->empresa) {
            Notification::make()
                ->title('❌ No hay empresa asignada')
                ->danger()
                ->send();
            return;
        }
        $data = $this->form->getRawState();
        $this->empresa->update([
            'gestoria_name' => $data['gestoria_nombre'] ?? $this->empresa->gestoria_name,
            'gestoria_cif' => $data['gestoria_cif'] ?? $this->empresa->gestoria_cif,
            'gestoria_phone' => $data['gestoria_telefono'] ?? $this->empresa->gestoria_phone,
            'gestoria_email' => $data['gestoria_email'] ?? $this->empresa->gestoria_email,
        ]);
        Notification::make()
            ->title('✅ Gestoría actualizada')
            ->success()
            ->send();
    }

    public function guardarNotaInterna()
    {
        if (! $this->empresa) {
            Notification::make()
                ->title('❌ No hay empresa asignada')
                ->danger()
                ->send();
            return;
        }
        $data = $this->form->getRawState();
        $this->empresa->update([
            'internal_note' => $data['nota_interna'] ?? null,
        ]);
        Notification::make()
            ->title('✅ Nota interna guardada')
            ->success()
            ->send();
    }

    public function submit(): void
    {
        if (! $this->empresa) {
            Notification::make()->title('❌ No hay empresa asignada')->danger()->send();
            return;
        }

        $data = $this->formData;

        Call::create([
            'user_id' => Auth::id(),
            'company_id' => $this->empresa->id,
            'call_date' => now(),
            'duration' => rand(60, 300),
            'status' => $data['resultado'],
            'recall_at' => in_array($data['resultado'], ['volver_a_llamar', 'contacto']) ? $data['fecha_rellamada'] : null,
            'motivo_desinteres' => $data['motivo_desinteres'] ?? null,
            'notes' => $data['comentarios'] ?? null,
            'contact_person' => $data['contacto'] ?? null,
        ]);

        match ($data['resultado']) {
            'no_interesa' => $this->empresa->updateQuietly(['assigned_operator_id' => null]),
            'no_contesta' => $this->reagendarEmpresaParaOtroOperador(),
            'error' => $this->empresa->updateQuietly(['deleted_at' => now()]),
            default => null,
        };

        Notification::make()
            ->title('✅ Llamada registrada correctamente')
            ->success()
            ->send();

        // Limpiar la empresa de la sesión solo después de registrar la llamada
        session()->forget('llamada_manual_empresa_id');
        $this->redirect('/dashboard/llamada-manual-page');
    }

    private function reagendarEmpresaParaOtroOperador(): void
    {
        $otroOperador = \App\Models\User::query()
            ->where('id', '!=', Auth::id())
            ->where('role_id', 1)
            ->inRandomOrder()
            ->first();

        if (! $otroOperador) {
            Notification::make()
                ->title('⚠️ No hay otros operadores disponibles para reagendar esta empresa.')
                ->warning()
                ->send();
            return;
        }

        $fecha = now()->addWeekday(3)->setTime(rand(10, 13), rand(0, 1) ? 0 : 30);

        $this->empresa->updateQuietly([
            'assigned_operator_id' => $otroOperador->id,
        ]);

        Call::create([
            'user_id' => Auth::id(),
            'company_id' => $this->empresa->id,
            'call_date' => now(),
            'duration' => rand(60, 120),
            'status' => 'no_contesta',
            'recall_at' => $fecha,
            'notes' => 'Llamada no contestada. Reagendada automáticamente.',
        ]);

        Notification::make()
            ->title('🔄 Empresa reagendada para otro operador el ' . $fecha->format('d/m/Y H:i'))
            ->success()
            ->send();
    }

    public function getHeading(): string
    {
        return '📞 Llamada Manual';
    }

    public function getTitle(): string
    {
        return $this->empresa ? '📞 Llamada a: ' . $this->empresa->name : '📞 Llamada Manual';
    }

    public function getContent(): string
    {
        if (! $this->empresa) {
            return '<div class="text-red-600 text-lg font-bold">🚫 No hay empresas disponibles para llamar ahora mismo.</div>';
        }

        return view('filament.pages.operator._empresa-info', ['empresa' => $this->empresa])->render();
    }

    public function getFormActions(): array
    {
        return [
            Action::make('guardar')
                ->label('✅ Guardar resultado de la llamada')
                ->submit('submit')
                ->color('success')
                ->button()
                ->keyBindings(['mod+s']),

            Action::make('marcarVenta')
                ->label('💰 Marcar como venta')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('¿Confirmar venta?')
                ->modalDescription('¿Estás seguro de que deseas crear una venta para esta empresa?')
                ->modalSubmitActionLabel('Sí, crear venta')
                ->modalCancelActionLabel('Cancelar') // <<< AÑADIDO Cancelar
                ->action(fn () => $this->redirigirAVenta()),
        ];
    }

    #[On('redirigir-venta')]
    public function redirigirAVenta(): void
    {
        if (! $this->empresa) {
            Notification::make()->title('❌ No hay empresa asignada')->danger()->send();
            return;
        }

        // Asegúrate de tener un producto asociado (puedes ajustar esta lógica según tu caso)
        $producto = \App\Models\Product::first(); // Usa el que quieras, o asócialo a la empresa si hay relación

        $this->redirect(\App\Filament\Resources\SaleResource::getUrl('create', [
            'empresa_id' => $this->empresa->id,
            'empresa_name' => $this->empresa->name,
            'empresa_address' => $this->empresa->address,
            'empresa_city' => $this->empresa->city,
            'empresa_province' => $this->empresa->province,
            'empresa_phone' => $this->empresa->phone,
            'empresa_mobile' => $this->empresa->mobile,
            'empresa_email' => $this->empresa->email,
            'empresa_activity' => $this->empresa->activity,
            'empresa_cnae' => $this->empresa->cnae,
            'empresa_cif' => $this->empresa->cif,
            'empresa_contact_person' => $this->empresa->contact_person,
            'empresa_iban' => $this->empresa->iban,
            'empresa_social_security' => $this->empresa->ss_company,
            'gestoria_name' => $this->empresa->gestoria_name,
            'gestoria_email' => $this->empresa->gestoria_email,
            'gestoria_phone' => $this->empresa->gestoria_phone,
            'representative_phone' => $this->empresa->representative_phone,

            // campos clave
            'operator_id' => auth()->id(),
            'sale_date' => now()->toDateString(),
            'product_id' => $producto?->id,
            'business_line_id' => $producto?->business_line_id,
        ]));
    }


}
