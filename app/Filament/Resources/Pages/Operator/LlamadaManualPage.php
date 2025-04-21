<?php

namespace App\Filament\Pages\Operator;

use App\Models\Company;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class LlamadaManualPage extends Page
{
    protected static string $view = 'filament.pages.operator.llamada-manual';

    public ?Company $empresa = null;

    public function mount(): void
    {
        $this->empresa = Company::query()
            ->where(function ($query) {
                $query->whereNull('assigned_operator_id')
                      ->orWhere('assigned_operator_id', Auth::id());
            })
            //->whereNull('deleted_at') // por si más adelante se usa SoftDeletes
            ->inRandomOrder()
            ->first();

        if ($this->empresa && $this->empresa->assigned_operator_id === null) {
            $this->empresa->updateQuietly([
                'assigned_operator_id' => Auth::id(),
            ]);
        }
    }

    public function getHeading(): string
    {
        return 'Llamada Manual';
    }
}
