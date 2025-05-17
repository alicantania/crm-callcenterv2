@php
    use Illuminate\Support\Str;
@endphp
<div>
    <h1>Reporte de Ventas por Operador ({{ $mes }}/{{ $anio }})</h1>
    <table class="table-auto w-full mb-8 border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 border">Operador</th>
                <th class="p-2 border">Total Ventas</th>
                <th class="p-2 border">Total Generado (€)</th>
                <th class="p-2 border">Comisión Total (€)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reporte as $row)
                <tr class="bg-gray-50">
                    <td class="p-2 border font-bold">{{ $row['operador']->name }} {{ $row['operador']->last_name ?? '' }}</td>
                    <td class="p-2 border text-center">{{ $row['total_ventas'] }}</td>
                    <td class="p-2 border text-right">{{ number_format($row['total_dinero'], 2, ',', '.') }}</td>
                    <td class="p-2 border text-right">{{ number_format($row['total_comision'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="p-0">
                        @foreach($row['detalle'] as $detalle)
                            <div class="p-2">
                                <h3 class="font-semibold text-blue-700">{{ $detalle['linea'] }}</h3>
                                <table class="table-auto w-full mb-2 border">
                                    <thead class="bg-blue-100">
                                        <tr>
                                            <th class="p-1 border">Empresa</th>
                                            <th class="p-1 border">CIF</th>
                                            <th class="p-1 border">Producto</th>
                                            <th class="p-1 border">Fecha</th>
                                            <th class="p-1 border">Importe (€)</th>
                                            <th class="p-1 border">Comisión (€)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($detalle['ventas'] as $venta)
                                            <tr>
                                                <td class="p-1 border">{{ $venta['empresa'] }}</td>
                                                <td class="p-1 border">{{ $venta['cif'] }}</td>
                                                <td class="p-1 border">{{ $venta['producto'] }}</td>
                                                <td class="p-1 border">{{ \Carbon\Carbon::parse($venta['fecha'])->format('d/m/Y') }}</td>
                                                <td class="p-1 border text-right">{{ number_format($venta['importe'], 2, ',', '.') }}</td>
                                                <td class="p-1 border text-right">{{ number_format($venta['comision'], 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-blue-50 font-semibold">
                                            <td colspan="4" class="p-1 border text-right">Total línea:</td>
                                            <td class="p-1 border text-right">{{ number_format($detalle['total_dinero'], 2, ',', '.') }}</td>
                                            <td class="p-1 border text-right">{{ number_format($detalle['total_comision'], 2, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{-- Aquí irán los botones de exportar a Excel y PDF --}}
</div>
