<div class="space-y-4 p-2">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Información del operador</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Nombre:</span>
                    <span class="font-medium">{{ $user->name }} {{ $user->last_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Email:</span>
                    <span class="font-medium">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Teléfono:</span>
                    <span class="font-medium">{{ $user->phone ?? 'No disponible' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Fecha de contrato:</span>
                    <span class="font-medium">{{ $user->contract_start_date ? $user->contract_start_date->format('d/m/Y') : 'No disponible' }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Métricas de rendimiento</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Total llamadas:</span>
                    <span class="font-medium">{{ number_format($user->calls_count, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Total ventas:</span>
                    <span class="font-medium">{{ number_format($user->sales_count, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Tasa de conversión:</span>
                    <span class="font-medium">
                        @if($user->calls_count > 0)
                            {{ number_format(($user->sales_count / $user->calls_count) * 100, 2, ',', '.') }}%
                        @else
                            0,00%
                        @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Total comisiones:</span>
                    <span class="font-medium">{{ number_format($user->total_commission ?? 0, 2, ',', '.') }} €</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Últimas actividades</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acción</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Descripción</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php
                        $activities = \App\Models\ActivityLog::where('user_id', $user->id)
                            ->latest('created_at')
                            ->limit(10)
                            ->get();
                    @endphp
                    
                    @forelse($activities as $activity)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $activity->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ ucfirst($activity->action) }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $activity->description ?? '-' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $activity->ip_address ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-400">
                                No hay actividades registradas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
