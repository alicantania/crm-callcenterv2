<?php

namespace App\Filament\Pages\Operator;

use App\Enums\CallStatus;
use App\Models\Call;
use App\Models\Company;
use App\Models\EmailRequest;
use App\Models\Sale;
use App\Models\Lead;
use App\Models\User;
use App\Models\Product;
use App\Notifications\EmailRequestNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Enums\CompanyStatus;
use Filament\Actions\Action;
use Livewire\Attributes\On;
use App\Filament\Resources\CompanyResource;

class LlamadaManualPage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    // Propiedades de la clase
    protected static ?string $navigationIcon  = 'heroicon-o-phone';
    protected static ?string $navigationLabel = 'Llamada Manual';
    protected static ?string $navigationGroup = 'Operador';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view            = 'filament.pages.operator.llamada-manual';

    // Persistencia multi-nivel: Livewire + sesión PHP
    #[\Livewire\Attributes\Persist('empresa_id')]
    public $empresa_id = null;

    public ?Company $empresa = null;
    public $formData = [];

    protected function getFormStatePath(): string
    {
        return 'formData';
    }

    /**
     * Al cargar el componente, chequea si ya hay una empresa en sesión o Livewire.
     * Si no hay, asigna una nueva.
     */
    public function mount(): void
    {
        // 1) Verificar si existe empresa en sesión PHP
        $empresa_id_sesion = session('operador_empresa_id');

        if ($empresa_id_sesion) {
            $empresaSesion = Company::find($empresa_id_sesion);
            if ($empresaSesion) {
                // Forzar uso de esa empresa
                $this->empresa    = $empresaSesion;
                $this->empresa_id = $empresa_id_sesion;
                $this->fillForm();

                // Verificar si ya registró llamada o venta hoy
                $hayVenta      = Sale::where('company_id', $this->empresa->id)->exists();
                $hayLlamadaHoy = $this->empresa->calls()
                    ->where('user_id', Auth::id())
                    ->whereDate('call_date', now()->toDateString())
                    ->exists();

                if (! $hayVenta && ! $hayLlamadaHoy) {
                    Notification::make()
                        ->title('⚠️ Acción requerida')
                        ->body('Debes registrar el resultado de la llamada para ' . $this->empresa->name . ' o crear una venta antes de pasar a otra empresa.')
                        ->warning()
                        ->persistent()
                        ->send();
                }

                // Detener mount() aquí
                return;
            } else {
                // Si la empresa ya no existe, limpiar sesión
                session()->forget('operador_empresa_id');
            }
        }

        // 2) Si Livewire ya tiene empresa_id
        if ($this->empresa_id) {
            $empresaLivewire = Company::find($this->empresa_id);
            if ($empresaLivewire) {
                $this->empresa    = $empresaLivewire;
                session(['operador_empresa_id' => $this->empresa_id]);
                $this->fillForm();
                return;
            }
        }

        // 3) Si viene por URL ?empresa_id=
        $empresa_id_request = request('empresa_id');
        if ($empresa_id_request) {
            $empresaSolicitada = Company::find($empresa_id_request);
            if ($empresaSolicitada) {
                $this->empresa    = $empresaSolicitada;
                $this->empresa_id = $empresaSolicitada->id;
                session(['operador_empresa_id' => $empresaSolicitada->id]);

                // Bloquear la empresa para este operador
                $empresaSolicitada->update([
                    'assigned_operator_id' => Auth::id(),
                    'locked_to_operator'   => true,
                    'locked_at'            => now(),
                ]);

                $this->fillForm();
                Notification::make()
                    ->title('✅ Empresa cargada para seguimiento')
                    ->body('Has accedido a: ' . $empresaSolicitada->name)
                    ->success()
                    ->send();
                return;
            }
        }

        // 4) Si no había empresa válida, asignar la siguiente
        if (! $this->empresa) {
            $nuevaEmpresa = $this->getNextEmpresa();
            if ($nuevaEmpresa) {
                session(['operador_empresa_id' => $nuevaEmpresa->id]);
            }
        }
    }

    /**
     * Retorna la siguiente empresa disponible (o null si no hay ninguna).
     * - Filtra por assigned_operator_id nulo o igual a tu ID.
     * - Solo activity = true.
     * - Solo locked_to_operator = false.
     * Bloquea la que devuelve asignándola a ti.
     */
    private function getNextEmpresa(): ?Company
    {
        // 1) Verificar si hay empresa en sesión PHP
        $empresa_id_sesion = session('operador_empresa_id');
        if ($empresa_id_sesion) {
            $empresaSesion = Company::find($empresa_id_sesion);
            if ($empresaSesion) {
                // Si aún no se registró venta ni llamada hoy, mantenerla
                $hayVenta      = Sale::where('company_id', $empresaSesion->id)->exists();
                $hayLlamadaHoy = $empresaSesion->calls()
                    ->where('user_id', Auth::id())
                    ->whereDate('call_date', now()->toDateString())
                    ->exists();

                if (! $hayVenta && ! $hayLlamadaHoy) {
                    $this->empresa    = $empresaSesion;
                    $this->empresa_id = $empresaSesion->id;
                    $this->fillForm();
                    Notification::make()
                        ->title('⚠️ Acción requerida')
                        ->body('Debes registrar el resultado de la llamada para ' . $empresaSesion->name . ' o crear una venta antes de pasar a otra empresa.')
                        ->warning()
                        ->persistent()
                        ->send();
                    return $empresaSesion;
                } else {
                    // Si ya hay llamada o venta, desbloquearla (si no está en seguimiento/contactada)
                    if (
                        $empresaSesion->status !== CompanyStatus::Seguimiento->value
                        && $empresaSesion->status !== CompanyStatus::Contactada->value
                    ) {
                        $empresaSesion->unlock();
                    }
                    // Limpiar referencias para buscar una nueva
                    $this->empresa_id = null;
                    $this->empresa    = null;
                    session()->forget('operador_empresa_id');
                }
            }
        }

        // 2) Verificar si Livewire aún tiene empresa_id válida
        if ($this->empresa_id && $this->empresa) {
            $hayVenta      = Sale::where('company_id', $this->empresa->id)->exists();
            $hayLlamadaHoy = $this->empresa->calls()
                ->where('user_id', Auth::id())
                ->whereDate('call_date', now()->toDateString())
                ->exists();

            if (! $hayVenta && ! $hayLlamadaHoy) {
                session(['operador_empresa_id' => $this->empresa->id]);
                Notification::make()
                    ->title('⚠️ Acción requerida')
                    ->body('Debes registrar el resultado de la llamada para ' . $this->empresa->name . ' o crear una venta antes de pasar a otra empresa.')
                    ->warning()
                    ->persistent()
                    ->send();
                return $this->empresa;
            } else {
                if (
                    $this->empresa->status !== CompanyStatus::Seguimiento->value
                    && $this->empresa->status !== CompanyStatus::Contactada->value
                ) {
                    $this->empresa->unlock();
                }
                $this->empresa_id = null;
                $this->empresa    = null;
                session()->forget('operador_empresa_id');
            }
        }

        // 3) Buscar una nueva empresa (asignada = null o a mí) + activa + desbloqueada
        $nextCompany = Company::query()
            ->where(function ($query) {
                $query->whereNull('assigned_operator_id')
                      ->orWhere('assigned_operator_id', Auth::id());
            })
            ->whereNotNull('activity')
            ->where(function ($query) {
                $query->whereNull('follow_up_date')
                      ->orWhere('follow_up_date', '<=', now());
            })
            ->where('locked_to_operator', false)
            ->inRandomOrder()
            ->first();

        if ($nextCompany) {
            // 4) Asignar y bloquear para el operador
            $this->empresa    = $nextCompany;
            $this->empresa_id = $nextCompany->id;
            session(['operador_empresa_id' => $nextCompany->id]);
            $this->fillForm();

            $nextCompany->update([
                'assigned_operator_id' => Auth::id(),
                'locked_to_operator'   => true,
                'locked_at'            => now(),
            ]);

            Notification::make()
                ->title('📢 Nueva empresa asignada')
                ->body('Se te ha asignado la empresa: ' . $nextCompany->name)
                ->info()
                ->send();

            return $this->empresa;
        }

        // 5) Si no se encontró ninguna
        Notification::make()
            ->title('ℹ️ No hay más empresas disponibles')
            ->body('No se encontraron empresas disponibles en este momento.')
            ->info()
            ->send();

        // Limpiar referencias
        $this->empresa    = null;
        $this->empresa_id = null;
        session()->forget('operador_empresa_id');
        $this->form->fill(); // Limpiar formulario
        return null;
    }

    /**
     * Llena todos los campos del formulario con los datos de $this->empresa
     */
    private function fillForm(): void
    {
        if (! $this->empresa) {
            return;
        }

        $formData = [
            // 1. Datos básicos
            'empresa_nombre'         => $this->empresa->name,
            'empresa_cif'            => $this->empresa->cif,
            'empresa_address'        => $this->empresa->address,
            'empresa_city'           => $this->empresa->city,
            'empresa_province'       => $this->empresa->province,
            'empresa_phone'          => $this->empresa->phone,
            'empresa_email'          => $this->empresa->email,
            'empresa_activity'       => $this->empresa->activity,
            'empresa_cnae'           => $this->empresa->cnae,
            'empresa_contact_person' => $this->empresa->contact_person,
            'empresa_iban'           => $this->empresa->iban,
            'empresa_ss_company'     => $this->empresa->ss_company,

            // 2. Representante legal
            'rep_legal_nombre'  => $this->empresa->legal_representative_name,
            'rep_legal_dni'     => $this->empresa->legal_representative_dni,
            'rep_legal_telefono'=> $this->empresa->representative_phone,

            // 3. Gestoría
            'gestoria_nombre'   => $this->empresa->gestoria_name,
            'gestoria_cif'      => $this->empresa->gestoria_cif,
            'gestoria_telefono' => $this->empresa->gestoria_phone,
            'gestoria_email'    => $this->empresa->gestoria_email,

            // 4. Nota interna
            'nota_interna'      => $this->empresa->internal_note,

            // 5. Curso interesado
            'curso_interesado'      => $this->empresa->curso_interesado,
            'precio_interesado'     => $this->empresa->precio_interesado,
            'comision_interesado'   => $this->empresa->comision_interesado,
            'modalidad_interesada'  => $this->empresa->modalidad_interesada,
            'fecha_interes'         => $this->empresa->fecha_interes,
            'observaciones_interes' => $this->empresa->observaciones_interes,
        ];

        $this->form->fill($formData);

        // Mostrar notificación del último contacto, si existe
        $ultimaLlamada = $this->empresa->calls()->latest('call_date')->first();
        if ($ultimaLlamada) {
            $mensajeFecha = is_string($ultimaLlamada->call_date)
                ? 'Fecha de último contacto: ' . $ultimaLlamada->call_date
                : 'Último contacto: ' . $ultimaLlamada->call_date->diffForHumans();

            Notification::make()
                ->title('ℹ️ Seguimiento de ' . $this->empresa->name)
                ->body($mensajeFecha . '. Persona de contacto: ' . $this->empresa->contact_person)
                ->info()
                ->send();
        }
    }

    #[On('empresa-updated')]
    public function handleEmpresaUpdated(): void
    {
        $this->fillForm();
    }

    /**
     * Redirige al formulario para crear una venta, marcando la empresa como contactada.
     */
    #[On('redirigir-venta')]
    public function redirectToCreateSale(): void
    {
        if (! $this->empresa) {
            Notification::make()
                ->title('❗ No hay empresa seleccionada')
                ->body('No se puede crear una venta sin tener una empresa seleccionada.')
                ->danger()
                ->send();
            return;
        }

        // Incrementar contador de contactos
        $this->empresa->incrementarContactos();

        // Registrar llamada "venta_realizada"
        $this->empresa->registerCall(
            CallStatus::VentaRealizada->value,
            'Llamada marcada como venta. Redirigido a formulario de venta.',
            rand(120, 300),
            $this->empresa->contact_person
        );

        // Marcar como contactada y bloqueada
        $this->empresa->update([
            'status'             => 'contactada',
            'assigned_operator_id'=> auth()->id(),
            'locked_to_operator' => true,
        ]);

        // Generar URL de creación de venta
        $saleCreateUrl = \App\Filament\Resources\SaleResource::getUrl('create', [
            'company_id' => $this->empresa->id,
        ]);

        Notification::make()
            ->title('✅ Empresa marcada como venta')
            ->body('Redirigiendo al formulario de venta...')
            ->success()
            ->send();

        redirect()->to($saleCreateUrl);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('LlamadaManualTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Empresa')
                            ->schema([
                                // Resumen de contacto previo
                                Forms\Components\Section::make('Resumen de contacto previo')
                                    ->visible(fn () => $this->empresa && $this->empresa->calls()->count() > 0)
                                    ->description(function () {
                                        $ultima = $this->empresa
                                            ? $this->empresa->calls()->latest('call_date')->first()
                                            : null;
                                        if (! $ultima) {
                                            return 'No hay contactos previos registrados.';
                                        }
                                        $fechaFormateada = is_string($ultima->call_date)
                                            ? $ultima->call_date
                                            : $ultima->call_date->format('d/m/Y H:i');
                                        return "Última llamada: {$fechaFormateada}";
                                    })
                                    ->collapsed(false)
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                // Datos de contacto
                                                Forms\Components\Group::make([
                                                    Forms\Components\Placeholder::make('contact_person')
                                                        ->label('Persona de contacto')
                                                        ->content(fn () => $this->empresa
                                                            ? $this->empresa->contact_person
                                                            : '-'),
                                                    Forms\Components\Placeholder::make('contact_phone')
                                                        ->label('Teléfono')
                                                        ->content(fn () => $this->empresa
                                                            ? $this->empresa->phone
                                                            : '-'),
                                                ]),
                                                // Datos de la última llamada
                                                Forms\Components\Group::make([
                                                    Forms\Components\Placeholder::make('ultima_llamada_estado')
                                                        ->label('Estado')
                                                        ->content(function () {
                                                            $ult = $this->empresa
                                                                ? $this->empresa->calls()->latest('call_date')->first()
                                                                : null;
                                                            if (! $ult) {
                                                                return '-';
                                                            }
                                                            $estados = [
                                                                'no_interesa'      => 'No interesa',
                                                                'no_contesta'      => 'No contesta',
                                                                'volver_a_llamar'  => 'Volver a llamar',
                                                                'contacto'         => 'Contacto realizado',
                                                                'error'            => 'Error',
                                                            ];
                                                            return $estados[$ult->status] ?? ucfirst($ult->status);
                                                        }),
                                                    Forms\Components\Placeholder::make('ultima_llamada_operador')
                                                        ->label('Operador')
                                                        ->content(function () {
                                                            $ult = $this->empresa
                                                                ? $this->empresa->calls()->latest('call_date')->first()
                                                                : null;
                                                            if (! $ult) {
                                                                return '-';
                                                            }
                                                            return $ult->user
                                                                ? $ult->user->name
                                                                : 'Desconocido';
                                                        }),
                                                ]),
                                            ]),
                                        Forms\Components\Placeholder::make('ultima_llamada_notas')
                                            ->label('Notas de la última llamada')
                                            ->content(function () {
                                                $ult = $this->empresa
                                                    ? $this->empresa->calls()->latest('call_date')->first()
                                                    : null;
                                                return $ult && $ult->notes
                                                    ? $ult->notes
                                                    : 'Sin notas';
                                            })
                                            ->columnSpanFull(),
                                    ]),
                                // Datos de la empresa
                                Forms\Components\Section::make('Datos de la empresa')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('empresa_nombre')
                                                    ->label('Nombre')
                                                    ->required()
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('empresa_cif')
                                                    ->label('CIF')
                                                    ->required(),
                                                Forms\Components\TextInput::make('empresa_activity')
                                                    ->label('Actividad'),
                                                Forms\Components\TextInput::make('empresa_cnae')
                                                    ->label('CNAE'),
                                                Forms\Components\TextInput::make('empresa_address')
                                                    ->label('Dirección')
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('empresa_city')
                                                    ->label('Ciudad'),
                                                Forms\Components\TextInput::make('empresa_province')
                                                    ->label('Provincia'),
                                                Forms\Components\TextInput::make('empresa_contact_person')
                                                    ->label('Persona de contacto')
                                                    ->helperText('Contacto importante para el seguimiento')
                                                    ->extraAttributes([
                                                        'class' => 'border-primary-600 bg-primary-50 dark:bg-primary-900/20',
                                                    ])
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('empresa_phone')
                                                    ->label('Teléfono')
                                                    ->tel(),
                                                Forms\Components\TextInput::make('empresa_email')
                                                    ->label('Email')
                                                    ->email(),
                                                Forms\Components\TextInput::make('empresa_iban')
                                                    ->label('IBAN'),
                                                Forms\Components\TextInput::make('empresa_ss_company')
                                                    ->label('Empresa SS'),
                                            ]),
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('guardar_empresa')
                                                ->label('Guardar datos de empresa')
                                                ->action('guardarEmpresa')
                                                ->color('primary')
                                                ->icon('heroicon-o-building-office-2'),
                                        ]),
                                    ]),
                            ]),
                        // Tab “Representante Legal”
                        Forms\Components\Tabs\Tab::make('Representante Legal')
                            ->schema([
                                Forms\Components\Section::make('Datos del representante legal')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('rep_legal_nombre')
                                                    ->label('Nombre')
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('rep_legal_dni')
                                                    ->label('DNI/NIE'),
                                                Forms\Components\TextInput::make('rep_legal_telefono')
                                                    ->label('Teléfono')
                                                    ->tel(),
                                            ]),
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('guardar_representante')
                                                ->label('Guardar datos del representante')
                                                ->action('guardarRepresentante')
                                                ->color('primary')
                                                ->icon('heroicon-o-user'),
                                        ]),
                                    ]),
                            ]),
                        // Tab “Gestoría”
                        Forms\Components\Tabs\Tab::make('Gestoría')
                            ->schema([
                                Forms\Components\Section::make('Datos de la gestoría')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('gestoria_nombre')
                                                    ->label('Nombre')
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('gestoria_cif')
                                                    ->label('CIF'),
                                                Forms\Components\TextInput::make('gestoria_telefono')
                                                    ->label('Teléfono')
                                                    ->tel(),
                                                Forms\Components\TextInput::make('gestoria_email')
                                                    ->label('Email')
                                                    ->email(),
                                            ]),
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('guardar_gestoria')
                                                ->label('Guardar datos de gestoría')
                                                ->action('guardarGestoria')
                                                ->color('primary')
                                                ->icon('heroicon-o-briefcase'),
                                        ]),
                                    ]),
                            ]),
                        // Tab “Historial de Llamadas”
                        Forms\Components\Tabs\Tab::make('Historial de Llamadas')
                            ->schema([
                                Forms\Components\Section::make('Historial de llamadas')
                                    ->schema([
                                        Forms\Components\View::make('filament.components.historial-llamadas')
                                            ->viewData(['empresa' => $this->empresa]),
                                    ]),
                            ])
                            ->badge(fn () => $this->empresa ? $this->empresa->calls()->count() : 0),
                        // Tab “Notas internas”
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
                                        ->icon('heroicon-o-pencil'),
                                ]),
                            ]),
                        // Tab “Curso Interesado”
                        Forms\Components\Tabs\Tab::make('Curso Interesado')
                            ->schema([
                                Forms\Components\Section::make('Información de interés del cliente')
                                    ->description('Consulta y edita lo que se habló con el cliente en la última llamada.')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Select::make('curso_interesado')
                                            ->label('Curso')
                                            ->options(fn () => Product::with('businessLine')->get()->mapWithKeys(function ($product) {
                                                $linea = $product->businessLine ? ' (' . $product->businessLine->name . ')' : '';
                                                return [$product->id => $product->name . $linea];
                                            })->toArray())
                                            ->searchable()
                                            ->reactive(),
                                        Forms\Components\TextInput::make('precio_interesado')
                                            ->label('Precio (€)')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->default(fn ($get) => $get('curso_interesado')
                                                ? Product::find($get('curso_interesado'))->price
                                                : ''),
                                        Forms\Components\TextInput::make('linea_negocio_interesada')
                                            ->label('Línea de negocio')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->default(fn ($get) => $get('curso_interesado')
                                                ? Product::with('businessLine')->find($get('curso_interesado'))->businessLine->name
                                                : ''),
                                        Forms\Components\TextInput::make('comision_interesada')
                                            ->label('Comisión (€)')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->default(fn ($get) => $get('curso_interesado')
                                                ? Product::find($get('curso_interesado'))->commission_percentage
                                                : ''),
                                        Forms\Components\Select::make('modalidad_interesada')
                                            ->label('Modalidad interesada')
                                            ->options([
                                                'online'     => 'Online',
                                                'presencial'=> 'Presencial',
                                                'mixto'     => 'Mixto',
                                            ])
                                            ->default(fn () => $this->empresa?->modalidad_interesada),
                                        Forms\Components\DatePicker::make('fecha_interes')
                                            ->label('Fecha de interés')
                                            ->default(fn () => $this->empresa?->fecha_interes),
                                        Forms\Components\Textarea::make('observaciones_interes')
                                            ->label('Observaciones de interés')
                                            ->rows(3)
                                            ->default(fn () => $this->empresa?->observaciones_interes),
                                    ]),
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('guardar_curso_interesado')
                                        ->label('Guardar interés de cliente')
                                        ->action(fn () => $this->guardarCursoInteresado())
                                        ->color('primary')
                                        ->icon('heroicon-o-academic-cap'),
                                ]),
                            ]),
                        // Tab “Solicitar Email”
                        Forms\Components\Tabs\Tab::make('Solicitar Email')
                            ->schema([
                                Forms\Components\Section::make('Solicitud de envío de email')
                                    ->description('El administrador enviará este email al cliente.')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Select::make('email_product_id')
                                            ->label('Curso a informar')
                                            ->options(fn () => Product::with('businessLine')->get()->mapWithKeys(function ($product) {
                                                $linea = $product->businessLine ? ' (' . $product->businessLine->name . ')' : '';
                                                return [$product->id => $product->name . $linea];
                                            })->toArray())
                                            ->searchable()
                                            ->required(),
                                        Forms\Components\TextInput::make('email_to')
                                            ->label('Email del destinatario')
                                            ->email()
                                            ->required()
                                            ->helperText('Se autocompleta con el email de la empresa, pero puedes modificarlo')
                                            ->afterStateHydrated(function ($component, $state) {
                                                if ($this->empresa && $this->empresa->email) {
                                                    $component->state($this->empresa->email);
                                                }
                                            }),
                                        Forms\Components\TextInput::make('email_contact_person')
                                            ->label('Persona de contacto')
                                            ->helperText('Se autocompleta con la persona de contacto de la empresa')
                                            ->afterStateHydrated(function ($component, $state) {
                                                if ($this->empresa && $this->empresa->contact_person) {
                                                    $component->state($this->empresa->contact_person);
                                                }
                                            }),
                                        Forms\Components\Textarea::make('email_notes')
                                            ->label('Notas para el administrador')
                                            ->placeholder('Incluye cualquier información relevante que deba incluirse en el email...')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('solicitar_email')
                                        ->label('Solicitar envío de email')
                                        ->action('solicitarEmail')
                                        ->color('warning')
                                        ->icon('heroicon-o-envelope'),
                                ]),
                            ]),
                        // Tab “Resultado de la llamada”
                        Forms\Components\Tabs\Tab::make('Resultado de la llamada')
                            ->schema([
                                Forms\Components\Section::make("📞 Resultado de la llamada")
                                    ->description("Completa la información con cuidado para registrar correctamente el resultado de la llamada.")
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('resultado')
                                                    ->label('Resultado')
                                                    ->options([
                                                        'no_interesa'     => 'No interesa',
                                                        'no_contesta'     => 'No contesta',
                                                        'volver_a_llamar' => 'Volver a llamar',
                                                        'contacto'        => 'Contacto',
                                                        'error'           => 'Error',
                                                    ])
                                                    ->reactive()
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('motivo_desinteres')
                                                    ->label('Motivo del desinterés')
                                                    ->placeholder('Ej: No tiene créditos, No quiere hacer cursos...')
                                                    ->visible(fn (callable $get) => $get('resultado') === 'no_interesa')
                                                    ->required(fn (callable $get) => $get('resultado') === 'no_interesa')
                                                    ->columnSpanFull(),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
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
                                    ->icon('heroicon-o-chat-bubble-bottom-center-text'),
                            ]),
                    ]),
            ])
            ->statePath('formData')
            ->model(Call::class);
    }

    /**
     * Al pulsar “Siguiente empresa”, limpiamos sesión y propiedades antes de buscar otra.
     */
    public function getSiguienteEmpresa()
    {
        session()->forget('operador_empresa_id');
        $this->empresa_id = null;
        $this->empresa    = null;
        $this->getNextEmpresa();
    }

    /**
     * Registra una llamada y libera la empresa actual o la mueve a “Mis Contactos”.
     */
    public function submit(): void
    {
        if (! $this->empresa) {
            Notification::make()
                ->title('❔ No hay empresa asignada')
                ->danger()
                ->send();
            return;
        }

        $data = $this->formData;

        // Validar que se haya seleccionado un resultado
        if (empty($data['resultado'])) {
            Notification::make()
                ->title('⚠️ Debes seleccionar un resultado de llamada')
                ->warning()
                ->send();
            return;
        }

        // 1) Incrementar contador de contactos
        $this->empresa->incrementarContactos();

        // 2) Crear registro de llamada (histórico)
        Call::create([
            'user_id'          => Auth::id(),
            'company_id'       => $this->empresa->id,
            'call_date'        => now(),
            'duration'         => rand(60, 300),
            'status'           => $data['resultado'],
            'recall_at'        => in_array($data['resultado'], ['volver_a_llamar', 'contacto'])
                                   ? $data['fecha_rellamada']
                                   : null,
            'motivo_desinteres'=> $data['motivo_desinteres'] ?? null,
            'notes'            => $data['comentarios'] ?? null,
            'contact_person'   => $data['contacto'] ?? null,
        ]);

        // 3) Actualizar persona de contacto si cambió
        if (! empty($data['contacto']) && $data['contacto'] != $this->empresa->contact_person) {
            $this->empresa->update(['contact_person' => $data['contacto']]);
        }

        // 4) Procesar según resultado
        switch ($data['resultado']) {
            case 'no_interesa':
                // La empresa se descarta temporalmente
                $fechaProximoContacto = now()->addMonths(3);
                $this->empresa->update([
                    'status'               => 'descartada',
                    'assigned_operator_id' => null,
                    'locked_to_operator'   => false,
                    'follow_up_date'       => $fechaProximoContacto,
                    'follow_up_notes'      => 'No interesa. Volver a contactar después de 3 meses.',
                ]);
                // Liberar y cargar siguiente
                $this->empresa_id = null;
                $this->empresa    = null;
                session()->forget('operador_empresa_id');
                Notification::make()
                    ->title('✅ Llamada registrada correctamente')
                    ->info()
                    ->send();
                $this->getNextEmpresa();
                return;

            case 'no_contesta':
                // Si lleva 3 intentos, se descarta un mes; si no, se programa 1–2 días después
                $intentosNoContesta = $this->empresa->calls()
                    ->where('status', 'no_contesta')
                    ->count();

                if ($intentosNoContesta >= 3) {
                    $fechaProximoContacto = now()->addMonth();
                    $this->empresa->update([
                        'status'               => 'descartada',
                        'assigned_operator_id' => null,
                        'locked_to_operator'   => false,
                        'follow_up_date'       => $fechaProximoContacto,
                        'follow_up_notes'      => 'No contesta después de 3 intentos. Volver a intentar en 1 mes.',
                    ]);
                    Notification::make()
                        ->title('⚠️ La empresa no contesta después de 3 intentos')
                        ->body('Se intentará contactar de nuevo en 1 mes')
                        ->warning()
                        ->send();
                } else {
                    $fechaProximoContacto = now()->addDays(rand(1, 2))->setHour(rand(9, 17));
                    $this->empresa->update([
                        'status'               => 'pendiente',
                        'assigned_operator_id' => null,
                        'locked_to_operator'   => false,
                        'follow_up_date'       => $fechaProximoContacto,
                        'follow_up_notes'      => 'No contestó la llamada. Intento ' . ($intentosNoContesta + 1) . ' de 3.',
                    ]);
                    Notification::make()
                        ->title('ℹ️ Empresa programada para otro operador')
                        ->body('La empresa estará disponible para otro operador el ' . $fechaProximoContacto->format('d/m/Y H:i'))
                        ->info()
                        ->send();
                }
                // Liberar y cargar siguiente
                $this->empresa_id = null;
                $this->empresa    = null;
                session()->forget('operador_empresa_id');
                Notification::make()
                    ->title('✅ Llamada registrada correctamente')
                    ->info()
                    ->send();
                $this->getNextEmpresa();
                return;

            case 'volver_a_llamar':
                if (empty($data['fecha_rellamada'])) {
                    Notification::make()
                        ->title('⚠️ Debes seleccionar una fecha para volver a llamar')
                        ->warning()
                        ->send();
                    return;
                }
                $fechaRe = is_string($data['fecha_rellamada'])
                    ? $data['fecha_rellamada']
                    : $data['fecha_rellamada']->format('Y-m-d H:i:s');

                // La empresa pasa a “seguimiento” y se desbloquea
                $this->empresa->update([
                    'status'               => 'seguimiento',
                    'assigned_operator_id' => Auth::id(),
                    'locked_to_operator'   => false,
                    'follow_up_date'       => $fechaRe,
                    'follow_up_notes'      => $data['comentarios'] ?? 'Volver a llamar según lo acordado.',
                ]);

                Notification::make()
                    ->title('✅ Empresa en seguimiento')
                    ->body('Se ha guardado en “Mis Contactos” para llamar el ' .
                           (is_string($data['fecha_rellamada'])
                               ? $data['fecha_rellamada']
                               : $data['fecha_rellamada']->format('d/m/Y H:i')) .
                           '.')
                    ->success()
                    ->send();

                // Liberar y cargar siguiente
                $this->empresa_id = null;
                $this->empresa    = null;
                session()->forget('operador_empresa_id');
                Notification::make()
                    ->title('🕐 Cargando siguiente empresa…')
                    ->info()
                    ->send();
                $this->getNextEmpresa();
                return;

            case 'contacto':
                $updates = [
                    'status'               => 'contactada',
                    'assigned_operator_id' => Auth::id(),
                    'locked_to_operator'   => false,
                ];
                if (! empty($data['fecha_rellamada'])) {
                    $fechaRe = is_string($data['fecha_rellamada'])
                        ? $data['fecha_rellamada']
                        : $data['fecha_rellamada']->format('Y-m-d H:i:s');
                    $updates['follow_up_date']  = $fechaRe;
                    $updates['follow_up_notes'] = $data['comentarios'] ?? 'Seguimiento de contacto realizado.';
                }
                $this->empresa->update($updates);

                Notification::make()
                    ->title('✅ Contacto registrado')
                    ->body('Se ha guardado en “Mis Contactos” con estado Contactada' .
                           (! empty($data['fecha_rellamada'])
                               ? ' y seguimiento para ' . (is_string($data['fecha_rellamada'])
                                   ? $data['fecha_rellamada']
                                   : $data['fecha_rellamada']->format('d/m/Y H:i'))
                               : '') .
                           '.')
                    ->success()
                    ->send();

                // Liberar y cargar siguiente
                $this->empresa_id = null;
                $this->empresa    = null;
                session()->forget('operador_empresa_id');
                Notification::make()
                    ->title('🕐 Cargando siguiente empresa…')
                    ->info()
                    ->send();
                $this->getNextEmpresa();
                return;

            case 'error':
                $this->empresa->update([
                    'status'               => 'error',
                    'assigned_operator_id' => null,
                    'locked_to_operator'   => false,
                    'active'               => false,
                ]);

                Notification::make()
                    ->title('⚠️ Empresa marcada como error')
                    ->body('Se ha notificado al administrador para revisar esta empresa.')
                    ->warning()
                    ->send();

                // Liberar y cargar siguiente
                $this->empresa_id = null;
                $this->empresa    = null;
                session()->forget('operador_empresa_id');
                Notification::make()
                    ->title('🕐 Cargando siguiente empresa…')
                    ->info()
                    ->send();
                $this->getNextEmpresa();
                return;
        }

        // Por seguridad, si no entró en ningún case con return:
        $this->reset('formData');
        $this->empresa_id = null;
        $this->empresa    = null;
        session()->forget('operador_empresa_id');
        Notification::make()
            ->title('✅ Llamada registrada')
            ->info()
            ->send();
        $this->getNextEmpresa();
    }

    /**
     * Registra una venta para la empresa actual y libera la empresa.
     */
    public function registrarVenta()
    {
        if (! $this->empresa) {
            Notification::make()
                ->title('⚠️ Error')
                ->body('No hay ninguna empresa asignada')
                ->danger()
                ->send();
            return;
        }

        // Marcar empresa como vendida y desbloquear
        $this->empresa->update([
            'status'             => 'vendida',
            'locked_to_operator' => false,
            'follow_up_date'     => null,
            'follow_up_notes'    => 'Convertida a venta por ' . Auth::user()->name,
        ]);

        // Registrar llamada de venta
        Call::create([
            'user_id'        => Auth::id(),
            'company_id'     => $this->empresa->id,
            'call_date'      => now(),
            'duration'       => rand(180, 600),
            'status'         => 'venta',
            'notes'          => 'Cliente ha confirmado la venta',
            'contact_person' => $this->empresa->contact_person,
        ]);

        Notification::make()
            ->title('✅ Venta registrada')
            ->body('La empresa ' . $this->empresa->name . ' ha sido marcada como vendida y ya no aparecerá en las llamadas.')
            ->success()
            ->send();

        // Liberar empresa actual
        $this->empresa    = null;
        $this->empresa_id = null;
        session()->forget('operador_empresa_id');

        // Asignar una nueva empresa
        $this->getNextEmpresa();

        // Redirigir al formulario de venta
        $params = http_build_query([
            'empresa_name' => $this->empresa->name,
            'empresa_cif'  => $this->empresa->cif ?? '',
            'empresa_id'   => $this->empresa->id ?? '',
        ]);
        return redirect()->to('/dashboard/resources/sales/create?' . $params);
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
            'name'            => $data['empresa_nombre'] ?? $this->empresa->name,
            'cif'             => $data['empresa_cif'] ?? $this->empresa->cif,
            'address'         => $data['empresa_address'] ?? $this->empresa->address,
            'city'            => $data['empresa_city'] ?? $this->empresa->city,
            'province'        => $data['empresa_province'] ?? $this->empresa->province,
            'phone'           => $data['empresa_phone'] ?? $this->empresa->phone,
            'email'           => $data['empresa_email'] ?? $this->empresa->email,
            'activity'        => $data['empresa_activity'] ?? $this->empresa->activity,
            'cnae'            => $data['empresa_cnae'] ?? $this->empresa->cnae,
            'contact_person'  => $data['empresa_contact_person'] ?? $this->empresa->contact_person,
            'iban'            => $data['empresa_iban'] ?? $this->empresa->iban,
            'ss_company'      => $data['empresa_ss_company'] ?? $this->empresa->ss_company,
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
            'legal_representative_dni'  => $data['rep_legal_dni'] ?? $this->empresa->legal_representative_dni,
            'representative_phone'      => $data['rep_legal_telefono'] ?? $this->empresa->representative_phone,
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
            'gestoria_name'   => $data['gestoria_nombre'] ?? $this->empresa->gestoria_name,
            'gestoria_cif'    => $data['gestoria_cif'] ?? $this->empresa->gestoria_cif,
            'gestoria_phone'  => $data['gestoria_telefono'] ?? $this->empresa->gestoria_phone,
            'gestoria_email'  => $data['gestoria_email'] ?? $this->empresa->gestoria_email,
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
            ->title('Nota interna guardada')
            ->success()
            ->send();

        $admins = User::whereHas('role', fn ($query) => $query->where('name', 'Admin'))->get();
        foreach ($admins as $admin) {
            if ($admin->id == Auth::id()) {
                continue;
            }
            Notification::make()
                ->title('Nueva nota interna en empresa')
                ->body('El operador ' . Auth::user()->name . ' ha añadido una nota interna en la empresa "' . $this->empresa->name . '".')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('Ver empresa')
                        ->url(CompanyResource::getUrl('view', ['record' => $this->empresa->id]))
                ])
                ->persistent()
                ->sendToDatabase($admin);
        }
    }

    public function guardarCursoInteresado()
    {
        if (! $this->empresa) {
            Notification::make()
                ->title('No hay empresa asignada')
                ->danger()
                ->send();
            return;
        }
        $data = $this->form->getRawState();
        $this->empresa->update([
            'curso_interesado'    => $data['curso_interesado'] ?? null,
            'modalidad_interesada'=> $data['modalidad_interesada'] ?? null,
            'fecha_interes'       => $data['fecha_interes'] ?? null,
            'observaciones_interes'=> $data['observaciones_interes'] ?? null,
        ]);

        Notification::make()
            ->title('✅ Interés del cliente guardado')
            ->success()
            ->send();

        $productoNombre = 'No especificado';
        if (! empty($data['curso_interesado'])) {
            $producto = Product::find($data['curso_interesado']);
            if ($producto) {
                $productoNombre = $producto->name;
            }
        }
        $admins = User::whereHas('role', fn ($query) => $query->where('name', 'Admin'))->get();
        foreach ($admins as $admin) {
            if ($admin->id == Auth::id()) {
                continue;
            }
            Notification::make()
                ->title('🎯 Cliente interesado en curso')
                ->body('El operador ' . Auth::user()->name . ' ha registrado interés de la empresa "' . $this->empresa->name . '" en el producto: ' . $productoNombre)
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('Ver empresa')
                        ->url(CompanyResource::getUrl('view', ['record' => $this->empresa->id]))
                ])
                ->persistent()
                ->sendToDatabase($admin);
        }
    }

    public function solicitarEmail(): void
    {
        if (! $this->empresa) {
            Notification::make()
                ->title('❌ No hay empresa asignada')
                ->body('Debes tener una empresa asignada para solicitar un email')
                ->danger()
                ->send();
            return;
        }

        $data = $this->form->getState();
        $emailData = [
            'email_product_id'    => $data['email_product_id'] ?? null,
            'email_to'            => $data['email_to'] ?? null,
            'email_contact_person'=> $data['email_contact_person'] ?? null,
            'email_notes'         => $data['email_notes'] ?? null,
        ];

        if (empty($emailData['email_product_id']) || empty($emailData['email_to'])) {
            Notification::make()
                ->title('⚠️ Faltan datos requeridos')
                ->body('Debe seleccionar un curso e indicar un email de destino')
                ->warning()
                ->send();
            return;
        }

        if (! filter_var($emailData['email_to'], FILTER_VALIDATE_EMAIL)) {
            Notification::make()
                ->title('⚠️ Email inválido')
                ->body('El formato del email proporcionado no es válido')
                ->warning()
                ->send();
            return;
        }

        try {
            $producto       = Product::findOrFail($emailData['email_product_id']);
            $productoNombre = $producto->name;

            $emailRequest = EmailRequest::create([
                'company_id'     => $this->empresa->id,
                'product_id'     => $emailData['email_product_id'],
                'email_to'       => $emailData['email_to'],
                'contact_person' => $emailData['email_contact_person'] ?? $this->empresa->contact_person ?? '',
                'notes'          => $emailData['email_notes'] ?? '',
                'requested_by_id'=> Auth::id(),
                'status'         => 'pending',
            ]);

            $contactPerson = $emailData['email_contact_person'] ?? $this->empresa->contact_person ?? '';
            $notes         = 'Solicitud de envío de email con información sobre: ' . $productoNombre;

            $this->empresa->registerCall(
                CallStatus::EmailEnviado->value,
                $notes,
                0,
                $contactPerson
            );

            if (! empty($emailData['email_contact_person'])
                && $emailData['email_contact_person'] != $this->empresa->contact_person
            ) {
                $this->empresa->update(['contact_person' => $emailData['email_contact_person']]);
            }

            if (! empty($emailData['email_to']) && $emailData['email_to'] != $this->empresa->email) {
                $this->empresa->update(['email' => $emailData['email_to']]);
            }

            $this->empresa->incrementarContactos();

            Notification::make()
                ->title('✅ Solicitud de email enviada')
                ->body('Email solicitado para ' . $this->empresa->name . ' con información de ' . $productoNombre)
                ->success()
                ->persistent()
                ->send();

            $admins = User::whereHas('role', fn ($q) => $q->where('name', 'Admin'))->get();
            foreach ($admins as $admin) {
                if ($admin->id === Auth::id()) {
                    continue;
                }
                $admin->notify(new EmailRequestNotification(
                    $emailRequest->id,
                    'pending',
                    $emailData['email_to'],
                    $this->empresa->name,
                    'Información de ' . $productoNombre
                ));
            }

            $this->form->fill([
                'email_product_id' => null,
                'email_notes'      => null,
                'email_to'         => $this->empresa->email,
            ]);
        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Error al procesar la solicitud')
                ->body('Ha ocurrido un error al procesar la solicitud de email: ' . $e->getMessage())
                ->danger()
                ->send();

            Log::error('Error al crear solicitud de email: ' . $e->getMessage(), [
                'empresa_id' => $this->empresa->id,
                'user_id'    => Auth::id(),
                'data'       => $emailData,
            ]);
        }
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('getNextEmpresa')
                ->label('Siguiente empresa')
                ->action('getSiguienteEmpresa')
                ->color('gray')
                ->visible(fn () => Auth::user()?->role_id == 1),

            Action::make('registrarLlamada')
                ->label('Registrar resultado')
                ->action('submit')
                ->color('success')
                ->visible(fn () => $this->empresa),

            Action::make('crearVenta')
                ->label('Crear venta')
                ->color('primary')
                ->icon('heroicon-o-currency-euro')
                ->visible(fn () => $this->empresa)
                ->requiresConfirmation()
                ->modalHeading('¿Confirmar venta?')
                ->modalDescription('¿Estás seguro de que deseas crear una venta para esta empresa?')
                ->modalSubmitActionLabel('Sí, crear venta')
                ->modalCancelActionLabel('Cancelar')
                ->action(fn () => $this->redirigirAVenta()),

            Action::make('salir')
                ->label('Salir')
                ->icon('heroicon-o-arrow-left')
                ->color('danger')
                ->action('exitManualCall'),
        ];
    }

    public function exitManualCall(): void
    {
        session()->forget('operador_empresa_id');
        $this->empresa_id = null;
        $this->empresa = null;
        $this->form->fill();
        $this->redirect(route('filament.dashboard.pages.mis-contactos-page'));
    }

    public function redirigirAVenta()
    {
        if (! $this->empresa) {
            Notification::make()
                ->title('❌ No hay empresa asignada')
                ->danger()
                ->send();
            return;
        }

        $this->empresa_id = $this->empresa->id;

        $this->empresa->update([
            'status'             => 'convertida',
            'locked_to_operator' => true,
        ]);

        $this->empresa->incrementarContactos();

        $url = \App\Filament\Resources\SaleResource::getUrl('create', [
            'empresa_name' => $this->empresa->name,
            'empresa_cif'  => $this->empresa->cif,
            'empresa_id'   => $this->empresa->id,
        ]);

        Notification::make()
            ->title('✅ Preparando venta')
            ->body('Vas a crear una venta para ' . $this->empresa->name . '. La empresa permanecerá asignada a ti.')
            ->success()
            ->send();

        $this->redirect($url);
    }

    protected function getRedirectUrlFromProducto($productoId)
    {
        $producto = Product::with('businessLine')->find($productoId);

        return \App\Filament\Resources\SaleResource::getUrl('create', [
            'empresa_name'      => $this->empresa->name,
            'empresa_cif'       => $this->empresa->cif,
            'empresa_id'        => $this->empresa->id,
            'product_id'        => $productoId,
            'business_line_id'  => $producto?->business_line_id,
        ]);
    }
}
