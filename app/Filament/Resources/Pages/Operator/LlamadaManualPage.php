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

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("📞 Datos de la llamada")
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('resultado')
                                ->label('Resultado de la llamada')
                                ->options([
                                    'no_interesa' => 'No interesa',
                                    'no_contesta' => 'No contesta',
                                    'volver_a_llamar' => 'Volver a llamar',
                                    'contacto' => 'Contacto',
                                    'error' => 'Error',
                                ])
                                ->reactive()
                                ->required(),

                                Forms\Components\TextInput::make('motivo_desinteres')
                                ->label('Motivo del desinterés')
                                ->placeholder('Ej: No tiene créditos, No quiere hacer cursos...')
                                ->visible(fn (callable $get) => $get('resultado') === 'no_interesa')
                                ->required(fn (callable $get) => $get('resultado') === 'no_interesa')
                                ->dehydrated(fn (callable $get) => $get('resultado') === 'no_interesa'),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\DateTimePicker::make('fecha_rellamada')
                                ->label('¿Cuándo volver a llamar?')
                                ->minutesStep(5)
                                ->withoutSeconds()
                                ->displayFormat('d/m/Y H:i')
                                ->native(false)
                                ->visible(fn (callable $get) => in_array($get('resultado'), ['volver_a_llamar', 'contacto'])),

                                Forms\Components\TextInput::make('contacto')
                                ->label('Persona de contacto')
                                ->placeholder('Nombre de quien atiende...')
                                ->dehydrated(fn (callable $get) => filled($get('contacto'))),
                        ]),

                        Forms\Components\Textarea::make('comentarios')
                            ->label('Comentarios adicionales')
                            ->autosize()
                            ->rows(3)
                            ->placeholder('Observaciones sobre la llamada...')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ])
            ->statePath('formData')
            ->model(Call::class);
    }

    public function submit(): void
    {
        if (! $this->empresa) {
            Notification::make()->title('❌ No hay empresa asignada')->danger()->send();
            return;
        }


        //dd($this->formData);

        $data = $this->formData;

        if ($data['resultado'] === 'no_contesta') {
            $this->reagendarEmpresaParaOtroOperador($data);
            return;
        }

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
            'venta' => redirect('/dashboard/sales/create?empresa_id=' . $this->empresa->id),
            default => null,
        };

        Notification::make()
            ->title('✅ Llamada registrada correctamente')
            ->success()
            ->send();

        $this->redirect('/dashboard/llamada-manual-page');
    }

    private function reagendarEmpresaParaOtroOperador(array $data): void
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
                ->action(function () {
                    if (! $this->empresa) {
                        Notification::make()->title('❌ No hay empresa asignada')->danger()->send();
                        return;
                    }

                    $this->redirect(\App\Filament\Resources\SaleResource::getUrl('create', [
                        'empresa_id' => $this->empresa->id,
                    ]));
                }),
        ];
    }

}
