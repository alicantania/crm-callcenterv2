<?php

namespace App\Filament\Pages\Operator;

use App\Models\Call;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class LlamadaManualPage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $view = 'filament.pages.operator.llamada-manual';

    public ?Company $empresa = null;

    // Estos son los campos del formulario
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

        $this->form->fill(); // Inicializa el formulario
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
                    ->required(),

                Forms\Components\DateTimePicker::make('fecha_rellamada')
                    ->label('¿Cuándo volver a llamar?')
                    ->visible(fn () => in_array($this->resultado, ['volver_a_llamar', 'contacto'])),

                Forms\Components\Textarea::make('comentarios')
                    ->label('Comentarios'),

                Forms\Components\TextInput::make('contacto')
                    ->label('Persona de contacto'),
            ])
            ->statePath('.'); // Importante para usar propiedades públicas
    }

    public function submit(): void
    {
        if (! $this->empresa) {
            Notification::make()
                ->title('No hay empresa asignada')
                ->danger()
                ->send();

            return;
        }

        // Guardar la llamada en la base de datos
        Call::create([
            'user_id' => Auth::id(),
            'company_id' => $this->empresa->id,
            'call_date' => now(),
            'duration' => rand(30, 300),
            'status' => $this->form->getState()['resultado'],
            'recall_at' => in_array($this->form->getState()['resultado'], ['volver_a_llamar', 'contacto']) 
                ? $this->form->getState()['fecha_rellamada']
                : null,
            'notes' => $this->form->getState()['comentarios'],
            'contact_person' => $this->form->getState()['contacto'],
        ]);

        // Gestionar el flujo según el resultado
        match ($this->form->getState()['resultado']) {
            'no_interesa', 'no_contesta' => $this->empresa->updateQuietly(['assigned_operator_id' => null]),
            'error' => $this->empresa->updateQuietly(['deleted_at' => now()]),
            'venta' => redirect('/dashboard/sales/create?empresa_id=' . $this->empresa->id),
            default => null,
        };

        Notification::make()
            ->title('✅ Resultado registrado correctamente')
            ->success()
            ->send();

        $this->redirect('/dashboard/llamada-manual-page');
    }


    public function getHeading(): string
    {
        return 'Llamada Manual';
    }
}
