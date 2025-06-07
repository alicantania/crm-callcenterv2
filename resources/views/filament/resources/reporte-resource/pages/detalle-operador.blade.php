<div class="space-y-6">
    <div class="text-center">
        <h2 class="text-xl font-bold">Detalle de Rendimiento: {{ $operador->name }}</h2>
        <p class="text-sm text-gray-500">Datos desde {{ $fechaInicio }} hasta hoy</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-lg mb-2">Métricas de Llamadas</h3>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt>Total Llamadas:</dt>
                    <dd class="font-medium">{{ $totalLlamadas }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Llamadas Efectivas:</dt>
                    <dd class="font-medium">{{ $llamadasEfectivas }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Tasa de Conversión:</dt>
                    <dd class="font-medium">{{ $tasaConversion }}%</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Duración Promedio:</dt>
                    <dd class="font-medium">{{ $duracionPromedio }} min</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-lg mb-2">Ventas Generadas</h3>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt>Total Ventas:</dt>
                    <dd class="font-medium">{{ $ventasGeneradas }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Ventas Tramitadas:</dt>
                    <dd class="font-medium text-green-600">{{ $ventasTramitadas }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Importe Tramitadas:</dt>
                    <dd class="font-medium text-green-600">{{ number_format($importeVentas, 2, ',', '.') }} €</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Ventas Anuladas:</dt>
                    <dd class="font-medium text-red-600">{{ $ventasAnuladas }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Importe Anulado:</dt>
                    <dd class="font-medium text-red-600">{{ number_format($importeAnulado, 2, ',', '.') }} €</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-lg mb-2">Ventas Pendientes</h3>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt>Ventas Pendientes/Incidentadas:</dt>
                    <dd class="font-medium text-amber-600">{{ $ventasPendientes }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Importe Pendiente:</dt>
                    <dd class="font-medium text-amber-600">{{ number_format($importePendiente, 2, ',', '.') }} €</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Importe Neto (Tramitadas - Anuladas):</dt>
                    <dd class="font-medium text-green-600">{{ number_format(max(0, $importeVentas - $importeAnulado), 2, ',', '.') }} €</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-lg mb-4">Distribución por Línea de Negocio</h3>
        
        @if(count($ventasPorLinea) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Línea de Negocio</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Total Ventas</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">% del Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($ventasPorLinea as $linea)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $linea->name }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $linea->total }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ $ventasGeneradas > 0 ? round(($linea->total / $ventasGeneradas) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 italic">No hay datos disponibles de ventas por línea de negocio.</p>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-lg mb-4">Últimas Llamadas</h3>
        
        @if(count($ultimasLlamadas) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Duración</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($ultimasLlamadas as $llamada)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($llamada->created_at)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $llamada->company_name }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $llamada->status == 'venta' ? 'bg-green-100 text-green-800' : 
                                           ($llamada->status == 'interesado' ? 'bg-blue-100 text-blue-800' : 
                                            ($llamada->status == 'no contesta' ? 'bg-gray-100 text-gray-800' : 
                                             ($llamada->status == 'no interesa' ? 'bg-red-100 text-red-800' : 
                                              ($llamada->status == 'volver a llamar' ? 'bg-yellow-100 text-yellow-800' : 
                                               'bg-gray-100 text-gray-800')))) }}">
                                        {{ ucfirst($llamada->status ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    @if(in_array($llamada->status, ['no contesta', 'error']))
                                        N/A
                                    @elseif($llamada->duration)
                                        {{ round($llamada->duration / 60, 1) }} min
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 italic">No hay datos disponibles de llamadas recientes.</p>
        @endif
    </div>

    <div class="flex justify-end">
        <button type="button" 
            x-on:click="close"
            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
            Cerrar
        </button>
    </div>
</div>
