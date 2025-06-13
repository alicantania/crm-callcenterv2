<?php

namespace App\Filament\Widgets\Superadmin;

use App\Models\ActivityLog;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SecurityStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    
    /**
     * Determina si el widget puede ser visto por el usuario actual.
     * Solo visible para superadmins (role_id = 4)
     */
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role_id === 4;
    }

    protected function getStats(): array
    {
        // Inicios de sesión hoy
        $loginsToday = ActivityLog::where('action', 'login')
            ->whereDate('created_at', now())
            ->count();
            
        // Inicios de sesión esta semana
        $loginsThisWeek = ActivityLog::where('action', 'login')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
            
        // Usuarios activos hoy
        $activeUsersToday = ActivityLog::where('action', 'login')
            ->whereDate('created_at', now())
            ->distinct('user_id')
            ->count('user_id');
            
        // Direcciones IP únicas
        $uniqueIPs = ActivityLog::where('action', 'login')
            ->whereDate('created_at', now())
            ->distinct('ip_address')
            ->count('ip_address');
            
        // Actividad por rol
        try {
            $activityByRole = User::join('activity_logs', 'users.id', '=', 'activity_logs.user_id')
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->whereDate('activity_logs.created_at', now())
                ->groupBy('roles.name')
                ->select('roles.name', DB::raw('count(*) as count'))
                ->pluck('count', 'name')
                ->toArray();
                
            // Formatear actividad por rol
            $roleActivityText = '';
            foreach ($activityByRole as $role => $count) {
                $roleActivityText .= "{$role}: {$count} acciones\n";
            }
        } catch (\Exception $e) {
            $roleActivityText = 'No disponible';
        }
            
        // Intentos fallidos de login
        $failedLogins = ActivityLog::where('action', 'failed_login')
            ->whereDate('created_at', now())
            ->count();

        return [
            Stat::make('Inicios de sesión hoy', $loginsToday)
                ->description('Total de inicios de sesión en el día actual')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->color('success'),
                
            Stat::make('Inicios de sesión esta semana', $loginsThisWeek)
                ->description('Total de inicios de sesión desde el lunes hasta hoy')
                ->icon('heroicon-o-calendar')
                ->color('info'),
                
            Stat::make('Usuarios activos hoy', $activeUsersToday)
                ->description('Usuarios únicos que han iniciado sesión hoy')
                ->icon('heroicon-o-user-group')
                ->color('warning'),
                
            Stat::make('IPs únicas', $uniqueIPs)
                ->description('Direcciones IP únicas utilizadas para iniciar sesión hoy')
                ->icon('heroicon-o-globe-alt')
                ->color('danger'),
                
            Stat::make('Intentos fallidos de login', $failedLogins)
                ->description('Intentos fallidos de inicio de sesión en el día actual')
                ->icon('heroicon-o-x-circle')
                ->color('gray'),
        ];
    }
}
