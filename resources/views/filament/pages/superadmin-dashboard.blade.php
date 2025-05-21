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

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Call Chart -->
        <x-filament::section>
            <x-slot name="heading">Llamadas por Período</x-slot>
            <div class="h-80">
                <!-- Representación visual de datos (placeholder) -->
                <div class="flex h-full items-end justify-between gap-2">
                    @foreach(range(1, 12) as $month)
                        @php $height = rand(10, 100); @endphp
                        <div class="flex flex-col items-center gap-2 w-full">
                            <div class="bg-primary-600 dark:bg-primary-400 rounded-t w-full" style="height: {{ $height }}%"></div>
                            <span class="text-xs text-gray-600 dark:text-gray-400">{{ substr(date('F', mktime(0, 0, 0, $month, 1)), 0, 3) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-filament::section>

        <!-- Sales Chart -->
        <x-filament::section>
            <x-slot name="heading">Ventas por Período</x-slot>
            <div class="h-80">
                <!-- Representación visual de datos (placeholder) -->
                <div class="flex h-full items-end justify-between gap-2">
                    @foreach(range(1, 12) as $month)
                        @php $height = rand(10, 100); @endphp
                        <div class="flex flex-col items-center gap-2 w-full">
                            <div class="bg-success-600 dark:bg-success-400 rounded-t w-full" style="height: {{ $height }}%"></div>
                            <span class="text-xs text-gray-600 dark:text-gray-400">{{ substr(date('F', mktime(0, 0, 0, $month, 1)), 0, 3) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-filament::section>
    </div>

    <!-- Commission Chart -->
    <x-filament::section>
        <x-slot name="heading">Comisiones por Operador</x-slot>
        <div class="h-80">
            <!-- Representación visual de datos (placeholder) -->
            <div class="flex h-full items-end justify-between gap-2">
                @php
                    $operators = \App\Models\User::where('role_id', 1)->take(10)->get(['id', 'name']);
                @endphp
                
                @foreach($operators as $operator)
                    @php $height = rand(10, 100); @endphp
                    <div class="flex flex-col items-center gap-2 w-full">
                        <div class="bg-info-600 dark:bg-info-400 rounded-t w-full" style="height: {{ $height }}%"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 truncate" style="max-width: 100%">{{ $operator->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament::page>
