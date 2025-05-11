<x-filament-panels::page>

   

    @section('content')
        <div class="space-y-6">
            {{-- Gráfica de ventas del último mes --}}
            <x-filament::card>
                <x-slot name="header">
                    <h2 class="text-lg font-bold">Ventas del último mes</h2>
                </x-slot>
                <canvas id="ventasMesChart" height="100"></canvas>
            </x-filament::card>

            {{-- Widgets con cifras clave --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <x-filament::card>
                    <h3 class="text-sm font-medium text-gray-500">Ventas este mes</h3>
                    <p class="text-2xl font-bold text-gray-800">{{ $ventasMes }}</p>
                </x-filament::card>

                <x-filament::card>
                    <h3 class="text-sm font-medium text-gray-500">Ventas hoy</h3>
                    <p class="text-2xl font-bold text-gray-800">{{ $ventasHoy }}</p>
                </x-filament::card>

                <x-filament::card>
                    <h3 class="text-sm font-medium text-gray-500">Llamadas ayer</h3>
                    <p class="text-2xl font-bold text-gray-800">{{ $llamadasAyer }}</p>
                </x-filament::card>

                <x-filament::card>
                    <h3 class="text-sm font-medium text-gray-500">Contactos para hoy</h3>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendientesHoy}}</p>
                </x-filament::card>
            </div>
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const ctx = document.getElementById('ventasMesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json(array_keys($ventasPorDia)),
                        datasets: [{
                            label: 'Ventas',
                            data: @json(array_values($ventasPorDia)),
                            borderColor: 'rgb(255, 165, 0)',
                            backgroundColor: 'rgba(255, 165, 0, 0.2)',
                            tension: 0.3,
                            fill: true,
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            </script>
        @endpush
 

</x-filament-panels::page>
