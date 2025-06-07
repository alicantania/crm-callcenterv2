<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanActivityLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clean {--days=30 : Número de días a mantener} {--type= : Tipo de logs a limpiar (login, update, etc.)} {--all : Limpiar todos los logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia los logs de actividad antiguos del sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $type = $this->option('type');
        $all = $this->option('all');

        if ($all) {
            if (!$this->confirm('¿Estás seguro de que quieres eliminar TODOS los logs de actividad? Esta acción no se puede deshacer.', false)) {
                $this->info('Operación cancelada.');
                return;
            }

            $count = ActivityLog::truncate();
            $this->info('Se han eliminado todos los logs de actividad.');
            return;
        }

        $query = ActivityLog::where('created_at', '<', now()->subDays($days));

        if ($type) {
            $query->where('action', $type);
            $this->info("Eliminando logs de tipo '{$type}' más antiguos de {$days} días...");
        } else {
            $this->info("Eliminando todos los logs más antiguos de {$days} días...");
        }

        $count = $query->count();
        
        if ($count === 0) {
            $this->info('No se encontraron logs para eliminar con los criterios especificados.');
            return;
        }

        if (!$this->confirm("Se eliminarán {$count} registros de logs. ¿Deseas continuar?", true)) {
            $this->info('Operación cancelada.');
            return;
        }

        $query->delete();
        
        $this->info("Se han eliminado {$count} registros de logs de actividad.");
    }
}
