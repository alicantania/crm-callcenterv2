<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\Superadmin\KpiWidget;
use App\Filament\Widgets\Superadmin\ActivityLogWidget;
use App\Filament\Widgets\Superadmin\LoginHistoryWidget;
use App\Filament\Widgets\Superadmin\SecurityStatsOverviewWidget;
use App\Filament\Widgets\Superadmin\OperatorActivityWidget;

class SuperadminDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationLabel = 'Panel Superadmin';
    protected static ?string $title = 'Panel de Control Superadmin';
    protected static ?string $slug = 'superadmin-dashboard';
    protected static ?string $navigationGroup = 'Superadmin';

    // Volvemos a usar la vista personalizada pero modificaremos su contenido
    protected static string $view = 'filament.pages.superadmin-dashboard';
    
    /**
     * Solo permitir acceso a superadmins
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->role_id === 4; // Superadmin
    }

    /**
     * Define los widgets disponibles para esta página
     */
    public function getWidgets(): array
    {
        return [
            KpiWidget::class,
            SecurityStatsOverviewWidget::class,
            OperatorActivityWidget::class,
            LoginHistoryWidget::class,
            ActivityLogWidget::class,
        ];
    }
    

}
