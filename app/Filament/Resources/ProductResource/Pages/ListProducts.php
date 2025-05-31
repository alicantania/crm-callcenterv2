<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        // Solo mostrar el botón de crear para roles de gerencia
        if (auth()->user()->role_id === 2) { // Gerencia
            return [
                Actions\CreateAction::make(),
            ];
        }
        
        return [];
    }
}
