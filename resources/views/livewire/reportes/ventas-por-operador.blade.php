@php
    use Illuminate\Support\Str;
@endphp
<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4" style="background-color: #f97316; color: white;">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold">
                        Reporte de Ventas por Operador - {{ $nombreMes }} {{ $anio }}
                    </h2>
                    <div>
                        <x-filament::button
                            icon="heroicon-o-document-arrow-down"
                            color="white"
                            outlined
                            wire:click="exportarExcel">
                            Exportar a Excel
                        </x-filament::button>
                    </div>
                </div>
            </div>

            <div class="p-4 border-b" style="background-color: #f3f4f6;">
                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center">
                        <span class="inline-block w-4 h-4 mr-2 rounded-full" style="background-color: #22c55e;"></span>
                        <span class="text-sm">Tramitada/Completada (Suma)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-4 h-4 mr-2 rounded-full" style="background-color: #ef4444;"></span>
                        <span class="text-sm">Anulada/Cancelada (Resta)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-4 h-4 mr-2 rounded-full" style="background-color: #eab308;"></span>
                        <span class="text-sm">Pendiente (Standby)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-4 h-4 mr-2 rounded-full" style="background-color: #f97316;"></span>
                        <span class="text-sm">Incidentada (Standby)</span>
                    </div>
                </div>
            </div>

            @if($reporte->isEmpty())
                <div class="p-8 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 text-gray-500 mb-4">
                        <x-heroicon-o-information-circle class="w-8 h-8" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No hay datos disponibles</h3>
                    <p class="mt-2 text-sm text-gray-500">No se encontraron ventas para el período seleccionado.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead style="background-color: #f3f4f6;">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Operador
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Ventas
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Generado (€)
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Comisión Total (€)
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($reporte as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full flex items-center justify-center font-bold" style="background-color: #e0f2fe; color: #0369a1;">
                                                    {{ substr($row['operador']->name, 0, 1) }}{{ substr($row['operador']->last_name ?? '', 0, 1) }}
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $row['operador']->name }} {{ $row['operador']->last_name ?? '' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $row['operador']->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" style="background-color: #dcfce7; color: #166534;">
                                            {{ $row['total_ventas'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <span class="font-bold">{{ number_format($row['total_dinero'], 2, ',', '.') }} €</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" style="color: #4f46e5;">
                                        <span class="font-bold">{{ number_format($row['total_comision'], 2, ',', '.') }} €</span>
                                    </td>
                                </tr>
                                
                                @if($mostrarDetalle && count($row['detalle']) > 0)
                                    <tr>
                                        <td colspan="4" class="px-0 py-0">
                                            <div class="border-t border-gray-200 px-4 py-3" style="background-color: #f9fafb;">
                                                @foreach($row['detalle'] as $detalle)
                                                    <div class="mb-4 last:mb-0">
                                                        <div class="flex justify-between items-center mb-2">
                                                            <h3 class="font-semibold" style="color: #4f46e5;">
                                                                <span class="inline-flex items-center">
                                                                    <x-heroicon-o-briefcase class="w-4 h-4 mr-1" />
                                                                    {{ $detalle['linea'] }}
                                                                </span>
                                                            </h3>
                                                            <div class="text-sm">
                                                                <span class="text-gray-500">Total línea:</span>
                                                                <span class="font-medium ml-2">{{ number_format($detalle['total_dinero'], 2, ',', '.') }} €</span>
                                                                <span class="font-medium ml-2" style="color: #4f46e5;">({{ number_format($detalle['total_comision'], 2, ',', '.') }} € comisión)</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="overflow-x-auto">
                                                            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-md">
                                                                <thead style="background-color: #f3f4f6;">
                                                                    <tr>
                                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                            Empresa
                                                                        </th>
                                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                            CIF
                                                                        </th>
                                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                            Producto
                                                                        </th>
                                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                            Fecha
                                                                        </th>
                                                                        <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                            Importe (€)
                                                                        </th>
                                                                        <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                            Comisión (€)
                                                                        </th>
                                                                        <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                            Estado
                                                                        </th>
                                                                        <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                            Contabiliza
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="bg-white divide-y divide-gray-200">
                                                                    @foreach($detalle['ventas'] as $venta)
                                                                        @php
                                                                            $statusColor = match(strtolower($venta['status'])) {
                                                                                'tramitada', 'completada', 'procesada' => ['bg' => '#dcfce7', 'text' => '#166534'],
                                                                                'anulada', 'cancelada' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                                                                                'incidentada' => ['bg' => '#ffedd5', 'text' => '#9a3412'],
                                                                                default => ['bg' => '#fef3c7', 'text' => '#92400e']
                                                                            };
                                                                            
                                                                            $contabilizaIcon = match($venta['contabilizar']) {
                                                                                1 => '<svg class="w-5 h-5" style="color: #16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
                                                                                -1 => '<svg class="w-5 h-5" style="color: #dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
                                                                                default => '<svg class="w-5 h-5" style="color: #ca8a04;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
                                                                            };
                                                                        @endphp
                                                                        <tr class="hover:bg-gray-50">
                                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                                                                {{ Str::limit($venta['empresa'], 30) }}
                                                                            </td>
                                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                                                                {{ $venta['cif'] }}
                                                                            </td>
                                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                                                                {{ Str::limit($venta['producto'], 30) }}
                                                                            </td>
                                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                                                                {{ \Carbon\Carbon::parse($venta['fecha'])->format('d/m/Y') }}
                                                                            </td>
                                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-medium">
                                                                                {{ number_format($venta['importe'], 2, ',', '.') }} €
                                                                            </td>
                                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-medium" style="color: #4f46e5;">
                                                                                {{ number_format($venta['comision'], 2, ',', '.') }} €
                                                                            </td>
                                                                            <td class="px-3 py-2 whitespace-nowrap text-center">
                                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" style="background-color: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }};">
                                                                                    {{ $venta['status'] }}
                                                                                </span>
                                                                            </td>
                                                                            <td class="px-3 py-2 whitespace-nowrap text-center">
                                                                                <div class="flex justify-center">
                                                                                    {!! $contabilizaIcon !!}
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot style="background-color: #f3f4f6;">
                            <tr>
                                <td class="px-6 py-3 text-left text-sm font-medium text-gray-900">
                                    Total general
                                </td>
                                <td class="px-6 py-3 text-center text-sm font-medium text-gray-900">
                                    {{ $reporte->sum('total_ventas') }}
                                </td>
                                <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                    {{ number_format($reporte->sum('total_dinero'), 2, ',', '.') }} €
                                </td>
                                <td class="px-6 py-3 text-right text-sm font-medium" style="color: #4f46e5;">
                                    {{ number_format($reporte->sum('total_comision'), 2, ',', '.') }} €
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
