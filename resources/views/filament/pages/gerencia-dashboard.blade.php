<div class="space-y-8 max-w-7xl mx-auto px-2 md:px-8" x-data="{ open: false, selected: [] }">
    <!-- Cargar los estilos de Select2 para selectores elegantes -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function initSelects() {
            jQuery('#businessLineSelect').select2({
                placeholder: 'Seleccionar líneas de negocio',
                width: '100%',
                dropdownCssClass: 'select2-dropdown-bordered',
                selectionCssClass: 'select2-selection-bordered',
                theme: 'classic'
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar al cargar
            setTimeout(initSelects, 500);

            // Reinicializar después de cada actualización de Livewire
            document.addEventListener('livewire:update', function() {
                setTimeout(initSelects, 100);
            });

            // También reinicializar en estos eventos Livewire
            document.addEventListener('livewire:load', function() {
                window.Livewire.hook('message.processed', function() {
                    setTimeout(initSelects, 100);
                });
            });
        });
    </script>
    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-8 text-left">Dashboard Gerencia</h2>

    <!-- Filtros estilo Filament demo -->
    <form wire:submit.prevent class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 mb-8 border border-gray-100 dark:border-gray-800">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="flex flex-col gap-2">
                <label for="userSelect" class="font-semibold text-gray-700 dark:text-gray-300">Ver métricas de:</label>
                <select id="userSelect" wire:model.defer="selectedUserId" class="filament-forms-select w-full rounded-lg border border-gray-200 dark:border-gray-700 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-900">
                    <option value="">Todos</option>
                    @foreach($this->users as $user)
                        <option value="{{ $user['id'] }}">
                            {{ $user['name'] }}
                            @if($user['role_id'] === 1) (Operador) @elseif($user['role_id'] === 2) (Admin) @elseif($user['role_id'] === 3) (Gerencia) @elseif($user['role_id'] === 4) (SuperAdmin) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-2">
                <label class="font-semibold text-gray-700 dark:text-gray-300 mb-1">Línea de negocio:</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($this->businessLines as $bl)
                        <button type="button"
                            wire:click="toggleBusinessLine('{{ $bl['id'] }}')"
                            class="px-4 py-2 rounded-lg border transition font-medium focus:outline-none
                                {{ in_array($bl['id'], is_array($selectedBusinessLines) ? $selectedBusinessLines : [])
                                    ? 'bg-primary-600 text-white border-primary-600 shadow'
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-700' }}">
                            {{ $bl['name'] }}
                        </button>
                    @endforeach
                </div>
                <small class="text-gray-400 mt-1">Haz clic para seleccionar una o varias líneas de negocio</small>
            </div>
            <div class="flex flex-col gap-2">
                <label for="startDate" class="font-semibold text-gray-700 dark:text-gray-300">Desde:</label>
                <input id="startDate" type="date" wire:model.defer="startDate" class="filament-forms-input w-full rounded-lg border border-gray-200 dark:border-gray-700 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-900" />
            </div>
            <div class="flex flex-col gap-2">
                <label for="endDate" class="font-semibold text-gray-700 dark:text-gray-300">Hasta:</label>
                <input id="endDate" type="date" wire:model.defer="endDate" class="filament-forms-input w-full rounded-lg border border-gray-200 dark:border-gray-700 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-900" />
            </div>
        </div>
        <div class="flex flex-wrap gap-4 items-end mt-6 justify-end">
            <button type="button" wire:click="$refresh" class="filament-button filament-button--primary px-6 py-2 rounded-lg shadow-sm bg-primary-600 hover:bg-primary-700 text-white font-semibold">Ver métricas</button>
        </div>
    </form>

    <!-- KPIs estilo Filament demo -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mt-8">
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm p-6 flex flex-col items-center">
            <div class="mb-1"><svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l4 4L21 7" /></svg></div>
            <span class="text-xs text-gray-500">Ventas totales</span>
            <span class="text-2xl font-bold text-primary-700 dark:text-primary-300">{{ $this->getStats()['total_sales'] }}</span>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm p-6 flex flex-col items-center">
            <div class="mb-1"><svg class="w-6 h-6 text-success-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 17l4 4 8-8" /></svg></div>
            <span class="text-xs text-gray-500">Ventas hoy</span>
            <span class="text-2xl font-bold text-success-700 dark:text-success-300">{{ $this->getStats()['sales_today'] }}</span>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm p-6 flex flex-col items-center">
            <div class="mb-1"><svg class="w-6 h-6 text-warning-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" /></svg></div>
            <span class="text-xs text-gray-500">Pendientes</span>
            <span class="text-2xl font-bold text-warning-700 dark:text-warning-300">{{ $this->getStats()['pending_sales'] }}</span>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm p-6 flex flex-col items-center">
            <div class="mb-1"><svg class="w-6 h-6 text-danger-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></div>
            <span class="text-xs text-gray-500">Devueltas</span>
            <span class="text-2xl font-bold text-danger-700 dark:text-danger-300">{{ $this->getStats()['returned_sales'] }}</span>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm p-6 flex flex-col items-center">
            <div class="mb-1"><svg class="w-6 h-6 text-info-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01" /></svg></div>
            <span class="text-xs text-gray-500">Comisiones generadas</span>
            <span class="text-2xl font-bold text-info-700 dark:text-info-300">{{ $this->getStats()['total_commissions'] ?? '0 €' }}</span>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm p-6 flex flex-col items-center">
            <div class="mb-1"><svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" /></svg></div>
            <span class="text-xs text-gray-500">Total vendido</span>
            <span class="text-2xl font-bold text-primary-700 dark:text-primary-300">{{ $this->getStats()['total_amount'] ?? '0 €' }}</span>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm p-6 flex flex-col items-center">
            <div class="mb-1"><svg class="w-6 h-6 text-info-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" /></svg></div>
            <span class="text-xs text-gray-500">Operadores</span>
            <span class="text-2xl font-bold text-info-700 dark:text-info-300">{{ $this->getStats()['operators'] }}</span>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm p-6 flex flex-col items-center">
            <div class="mb-1"><svg class="w-6 h-6 text-secondary-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" /></svg></div>
            <span class="text-xs text-gray-500">Admins</span>
            <span class="text-2xl font-bold text-secondary-700 dark:text-secondary-300">{{ $this->getStats()['admins'] }}</span>
        </div>
    </div>

    <!-- Gráficas estilo Filament demo -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold mb-4 text-gray-800 dark:text-gray-100 text-center">Ventas por {{ $groupBy === 'year' ? 'año' : 'mes' }}</h3>
            <canvas id="ventasChart" height="120"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold mb-4 text-gray-800 dark:text-gray-100 text-center">Comisiones por {{ $groupBy === 'year' ? 'año' : 'mes' }}</h3>
            <canvas id="comisionesChart" height="120"></canvas>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('chartDataUpdated', function (chartData) {
                renderCharts(chartData);
            });

            function renderCharts(chartData) {
                // Ventas Chart
                const ventasCtx = document.getElementById('ventasChart').getContext('2d');
                if (window.ventasChart) window.ventasChart.destroy();
                window.ventasChart = new Chart(ventasCtx, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Ventas',
                            data: chartData.ventas,
                            backgroundColor: 'rgba(79, 70, 229, 0.2)',
                            borderColor: 'rgba(79, 70, 229, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Comisiones Chart
                const comisionesCtx = document.getElementById('comisionesChart').getContext('2d');
                if (window.comisionesChart) window.comisionesChart.destroy();
                window.comisionesChart = new Chart(comisionesCtx, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Comisiones',
                            data: chartData.comisiones,
                            backgroundColor: 'rgba(16, 185, 129, 0.2)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });
    </script>

    <style>
        /* Incluyo los estilos CSS dentro del div raíz */
        .dashboard-custom .bg-white {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
    </style>
</div>
