<?php

namespace App\Livewire\Operator;

use App\Models\Company;
use App\Models\Call;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LlamadaManualHandler extends Component
{
    public ?Company $empresa = null;

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
            ->when(method_exists(Company::class, 'bootSoftDeletes'), fn($q) => $q->whereNull('deleted_at'))
            ->inRandomOrder()
            ->first();

        if ($this->empresa && is_null($this->empresa->assigned_operator_id)) {
            $this->empresa->updateQuietly([
                'assigned_operator_id' => Auth::id(),
            ]);
        }
    }

    public function guardarResultado(): void
    {
        if (! $this->empresa) {
            session()->flash('error', 'No hay empresa asignada');
            return;
        }

        // Guardamos la llamada
        Call::create([
            'user_id' => Auth::id(),
            'company_id' => $this->empresa->id,
            'call_date' => now(),
            'duration' => rand(60, 300),
            'status' => $this->resultado,
            'notes' => $this->comentarios,
            'contact_person' => $this->contacto,
            'recall_at' => in_array($this->resultado, ['volver_a_llamar', 'contacto']) ? $this->fecha_rellamada : null,
        ]);

        match ($this->resultado) {
            'no_interesa', 'no_contesta' => $this->liberarEmpresaYRedirigir(),
            'volver_a_llamar', 'contacto' => $this->avisarProgramada(),
            'venta' => $this->redirigirAVenta(),
            'error' => $this->eliminarEmpresaLogicamente(),
            default => session()->flash('error', 'Debes seleccionar un resultado válido'),
        };
    }

    protected function liberarEmpresaYRedirigir(): void
    {
        $this->empresa->updateQuietly(['assigned_operator_id' => null]);
        session()->flash('message', '🔁 Empresa liberada. Pasando a la siguiente...');
        $this->redirect('/dashboard/llamada-manual-page', navigate: true);
    }

    protected function avisarProgramada(): void
    {
        session()->flash('message', '📅 Llamada programada correctamente');
        $this->redirect('/dashboard/llamada-manual-page', navigate: true);
    }

    protected function redirigirAVenta(): void
    {
        session()->flash('message', '💰 Redirigiendo a formulario de venta...');
        $this->redirect('/dashboard/sales/create?empresa_id=' . $this->empresa->id, navigate: true);
    }

    protected function eliminarEmpresaLogicamente(): void
    {
        // Aquí puedes usar SoftDeletes o una columna personalizada
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->empresa))) {
            $this->empresa->delete(); // Soft delete
        } else {
            $this->empresa->updateQuietly(['eliminada_logicamente' => true]); // O campo personalizado
        }

        session()->flash('message', '❌ Empresa eliminada lógicamente.');
        $this->redirect('/dashboard/llamada-manual-page', navigate: true);
    }

    public function render()
    {
        return view('livewire.operator.llamada-manual-handler');
    }
}
