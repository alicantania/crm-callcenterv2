<!-- <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mt-6">
    <h3 class="text-lg font-semibold mb-4">Comparativa de Ventas por Operador</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">Operador</th>
                    <th class="px-4 py-2 text-left">Ventas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->ventasPorOperador as $operador => $total)
                    <tr>
                        <td class="px-4 py-2">{{ $operador }}</td>
                        <td class="px-4 py-2 font-bold">{{ $total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h3 class="text-lg font-semibold mt-8 mb-4">Evolución de Ventas por Día</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">Fecha</th>
                    <th class="px-4 py-2 text-left">Ventas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->ventasPorDia as $row)
                    <tr>
                        <td class="px-4 py-2">{{ $row['fecha'] }}</td>
                        <td class="px-4 py-2 font-bold">{{ $row['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div> -->
