<?php

namespace App\Filament\Resources\ReporteResource\Pages;

use App\Filament\Resources\ReporteResource;
use Filament\Resources\Pages\ListRecords;

class ListReportes extends ListRecords
{
    protected static string $resource = ReporteResource::class;
    public function getTitle(): string
    {
        return 'Reportes de Ventas';
    }
}
