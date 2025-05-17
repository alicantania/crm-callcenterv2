<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\Page;
use Filament\Actions;

class ViewSale extends Page
{
    protected static string $resource = SaleResource::class;
    protected static string $view = 'filament.resources.sale-resource.pages.view-sale';

    public $record;

    public function mount($record): void
    {
        $this->record = \App\Models\Sale::findOrFail($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            // No actions for view-only
        ];
    }
}
