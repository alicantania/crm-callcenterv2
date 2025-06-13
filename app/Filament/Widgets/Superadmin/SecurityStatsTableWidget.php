<?php

namespace App\Filament\Widgets\Superadmin;

use App\Models\ActivityLog;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SecurityStatsTableWidget extends BaseWidget
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

    public function table(Table $table): Table
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

        // Crear datos para la tabla
        $securityStats = collect([
            [
                'id' => 1,
                'metric' => 'Inicios de sesión hoy',
                'value' => $loginsToday,
                'description' => 'Total de inicios de sesión en el día actual'
            ],
            [
                'id' => 2,
                'metric' => 'Inicios de sesión esta semana',
                'value' => $loginsThisWeek,
                'description' => 'Total de inicios de sesión desde el lunes hasta hoy'
            ],
            [
                'id' => 3,
                'metric' => 'Usuarios activos hoy',
                'value' => $activeUsersToday,
                'description' => 'Usuarios únicos que han iniciado sesión hoy'
            ],
            [
                'id' => 4,
                'metric' => 'IPs únicas',
                'value' => $uniqueIPs,
                'description' => 'Direcciones IP únicas utilizadas para iniciar sesión hoy'
            ],
            [
                'id' => 5,
                'metric' => 'Actividad por rol',
                'value' => count($activityByRole ?? []),
                'description' => $roleActivityText
            ],
            [
                'id' => 6,
                'metric' => 'Intentos fallidos de login',
                'value' => $failedLogins,
                'description' => 'Intentos fallidos de inicio de sesión en el día actual'
            ],
        ]);

        // Crear un modelo anónimo para usar con la tabla
        $query = new class extends \Illuminate\Database\Eloquent\Model {
            protected $table = 'security_stats';
        };
        
        // Configurar la conexión para usar una colección en memoria
        \Illuminate\Support\Facades\DB::statement('CREATE TEMPORARY TABLE IF NOT EXISTS security_stats (id INTEGER, metric TEXT, value INTEGER, description TEXT)');
        
        // Insertar los datos en la tabla temporal
        foreach ($securityStats as $stat) {
            \Illuminate\Support\Facades\DB::table('security_stats')->insert([
                'id' => $stat['id'],
                'metric' => $stat['metric'],
                'value' => $stat['value'],
                'description' => $stat['description']
            ]);
        }
        
        return $table
            ->heading('🔒 Estadísticas de Seguridad')
            ->description('Métricas clave de seguridad y acceso al sistema')
            ->query($query::query())
            ->columns([
                Tables\Columns\TextColumn::make('metric')
                    ->label('Métrica')
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->wrap(),
            ])
            ->paginated(false);
    }
}
