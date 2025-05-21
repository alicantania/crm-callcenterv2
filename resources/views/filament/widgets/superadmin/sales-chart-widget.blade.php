<div>
    <div class="mb-4">
        <div class="flex justify-between items-center">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Ventas por periodo</h3>
            <div class="flex space-x-2">
                <button
                    wire:click="$set('period', 'day')"
                    class="px-3 py-1 text-xs font-medium rounded-md {{ $period === 'day' ? 'bg-primary-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}"
                >
                    Día
                </button>
                <button
                    wire:click="$set('period', 'week')"
                    class="px-3 py-1 text-xs font-medium rounded-md {{ $period === 'week' ? 'bg-primary-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}"
                >
                    Semana
                </button>
                <button
                    wire:click="$set('period', 'month')"
                    class="px-3 py-1 text-xs font-medium rounded-md {{ $period === 'month' ? 'bg-primary-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}"
                >
                    Mes
                </button>
            </div>
        </div>
    </div>

    <div class="h-64">
        <!-- Aquí iría el gráfico de recharts, usaremos un placeholder por ahora -->
        <div class="h-full flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="text-center">
                <p class="text-gray-500 dark:text-gray-400">Gráfico de ventas por {{ $period === 'day' ? 'hora' : ($period === 'week' ? 'día de la semana' : 'día del mes') }}</p>
                <p class="mt-2 text-sm text-gray-400">Para implementar con React/Recharts</p>
            </div>
        </div>
    </div>
</div>
