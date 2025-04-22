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

    public string $resultado = '';
    public ?string $fecha_rellamada = null;
    public ?string $comentarios = null;
    public ?string $contacto = null;

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
                Forms\Components\Select::make('resultado')
                    ->label('Resultado de la llamada')
                    ->options([
                        'no_interesa' => 'No interesa',
                        'no_contesta' => 'No contesta',
                        'volver_a_llamar' => 'Volver a llamar',
                        'contacto' => 'Contacto',
                        'venta' => 'Venta',
                        'error' => 'Error',
                    ])
                    ->reactive()
                    ->required(),

                Forms\Components\DateTimePicker::make('fecha_rellamada')
                    ->label('¿Cuándo volver a llamar?')
                    ->visible(fn (callable $get) => in_array($get('resultado'), ['volver_a_llamar', 'contacto']))
                    ->required(fn (callable $get) => in_array($get('resultado'), ['volver_a_llamar', 'contacto'])),
                

                Forms\Components\Textarea::make('comentarios')
                    ->label('Comentarios'),

                Forms\Components\TextInput::make('contacto')
                    ->label('Persona de contacto'),
            ])
            ->statePath('formData');
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
            'notes' => $data['comentarios'] ?? null,
            'contact_person' => $data['contacto'] ?? null,
        ]);

        match ($data['resultado']) {
            'no_interesa', 'no_contesta' => $this->empresa->updateQuietly(['assigned_operator_id' => null]),
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

    // public function getFormActions(): array
    // {
    //     return [
    //         Action::make('guardar')
    //             ->label('✅ Guardar resultado de la llamada')
    //             ->submit('submit')
    //             ->color('success')
    //             ->button()
    //             ->keyBindings(['mod+s']),
    //     ];
    // }
}
