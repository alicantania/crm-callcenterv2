<x-filament::page>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <!-- KPI Cards -->
        <x-filament::section>
            <div class="flex items-center gap-4">
                <div class="rounded-lg bg-primary-500 p-2">
                    <x-heroicon-o-building-office class="h-6 w-6 text-white" />
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Empresas</h3>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\Company::count() }}</div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center gap-4">
                <div class="rounded-lg bg-success-500 p-2">
                    <x-heroicon-o-phone class="h-6 w-6 text-white" />
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Llamadas Hoy</h3>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\Call::whereDate('created_at', today())->count() }}</div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center gap-4">
                <div class="rounded-lg bg-warning-500 p-2">
                    <x-heroicon-o-clipboard-document-list class="h-6 w-6 text-white" />
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Ventas Pendientes</h3>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\Sale::where('status', 'pendiente')->count() }}</div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center gap-4">
                <div class="rounded-lg bg-info-500 p-2">
                    <x-heroicon-o-users class="h-6 w-6 text-white" />
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Operadores Activos</h3>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\User::where('role_id', 1)->where('active', true)->count() }}</div>
                </div>
            </div>
        </x-filament::section>
    </div>

    <!-- Widgets de monitoreo y logs -->
    @foreach ($this->getWidgets() as $widget)
        @if ($widget::canView())
            @livewire($widget)
        @endif
    @endforeach
</x-filament::page>
