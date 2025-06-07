<?php

namespace App\Filament\Widgets\Superadmin;

use App\Models\ActivityLog;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;

class SecurityStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
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
        } catch (\Exception $e) {
            // Si hay error en la consulta, simplemente devolvemos un array vacío
            $activityByRole = [];
        }
            
        // Formatear actividad por rol
        $roleActivityText = '';
        foreach ($activityByRole as $role => $count) {
            $roleActivityText .= "{$role}: {$count}\n";
        }
        
        // Últimos inicios de sesión fallidos (si se registran)
        $failedLogins = ActivityLog::where('action', 'login_failed')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->count();
        
        return [
            Stat::make('Inicios de sesión hoy', $loginsToday)
                ->description('Total de inicios de sesión en el día actual')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Usuarios activos hoy', $activeUsersToday)
                ->description('Usuarios únicos que han iniciado sesión hoy')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
                
            Stat::make('IPs únicas hoy', $uniqueIPs)
                ->description('Direcciones IP distintas detectadas hoy')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('warning'),
                
            Stat::make('Inicios esta semana', $loginsThisWeek)
                ->description('Total de inicios de sesión en la semana actual')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
                
            Stat::make('Actividad por rol', $roleActivityText ?: 'Sin datos')
                ->description('Distribución de actividad por tipo de usuario')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('gray'),
                
            Stat::make('Intentos fallidos', $failedLogins)
                ->description('Intentos fallidos de inicio de sesión (7 días)')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color($failedLogins > 0 ? 'danger' : 'success'),
        ];
    }
}
