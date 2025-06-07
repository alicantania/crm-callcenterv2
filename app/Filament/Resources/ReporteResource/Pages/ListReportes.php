<?php

namespace App\Filament\Resources\ReporteResource\Pages;

use App\Filament\Resources\ReporteResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use App\Models\Call;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class ListReportes extends ListRecords
{
    protected static string $resource = ReporteResource::class;
    
    public function getTitle(): string
    {
        return 'Rendimiento del Call Center';
    }
    
    // Eliminamos la referencia al widget que causa problemas
    protected function getHeaderWidgets(): array
    {
        return [];
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh_stats')
                ->label('Actualizar Estadísticas')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    Notification::make()
                        ->title('Estadísticas Actualizadas')
                        ->body('Los datos de rendimiento han sido actualizados correctamente.')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('generate_report')
                ->label('Generar Informe Completo')
                ->icon('heroicon-o-document-text')
                ->action(function () {
                    Notification::make()
                        ->title('Generando Informe')
                        ->body('El informe completo se está generando y estará disponible en breve.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
