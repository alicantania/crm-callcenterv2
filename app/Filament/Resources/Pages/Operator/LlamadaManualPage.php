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
            ->when(Auth::check(), function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('assigned_operator_id')
                      ->orWhere('assigned_operator_id', Auth::id());
                });
            })
            ->inRandomOrder()
            ->first();

        if ($this->empresa && is_null($this->empresa->assigned_operator_id)) {
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
