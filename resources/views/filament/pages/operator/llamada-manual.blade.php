<x-filament::page>
    @if ($empresa)
        <div x-data="{ confirmVenta: false }" class="space-y-6">

            <!-- Empresa -->
            <div class="text-xl font-bold text-gray-800">
                Empresa seleccionada: {{ $empresa->name }}
            </div>

            <!-- Info de empresa -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded shadow">
                <div><strong>📍 Dirección:</strong> {{ $empresa->address }}</div>
                <div><strong>🏙️ Ciudad:</strong> {{ $empresa->city }}</div>
                <div><strong>🌍 Provincia:</strong> {{ $empresa->province }}</div>
                <div><strong>📞 Teléfono:</strong> {{ $empresa->phone }}</div>
                <div><strong>✉️ Email:</strong> {{ $empresa->email }}</div>
                <div><strong>🏢 Actividad:</strong> {{ $empresa->activity }}</div>
                <div><strong>🔢 CNAE:</strong> {{ $empresa->cnae }}</div>
            </div>

            <!-- Formulario -->
            <form wire:submit.prevent="submit">
                {{ $this->form }}

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-filament::button
                        type="submit"
                        color="success"
                        class="w-full text-lg py-3"
                    >
                        ✅ Guardar resultado de la llamada
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        color="warning"
                        class="w-full text-lg py-3"
                        x-on:click="confirmVenta = true"
                    >
                        💰 Marcar como venta
                    </x-filament::button>
                </div>
            </form>

            <!-- Modal Confirmación CORREGIDO -->
            <div
                x-show="confirmVenta"
                x-cloak
                class="fixed inset-0 flex items-center justify-center bg-black/50 z-50"
            >
                <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">¿Confirmar venta?</h2>
                    <p class="mb-6 text-gray-600">¿Estás seguro de que deseas marcar esta llamada como una venta?</p>

                    <div class="flex justify-end gap-4">
                        <x-filament::button
                            color="gray"
                            size="md"
                            type="button"
                            x-on:click="confirmVenta = false"
                        >
                            Cancelar
                        </x-filament::button>

                        <x-filament::button
                            color="warning"
                            size="md"
                            type="button"
                            x-on:click="
                                confirmVenta = false;
                                $dispatch('redirigir-venta');
                            "
                        >
                            Sí, crear venta
                        </x-filament::button>
                    </div>
                </div>
            </div>



        </div>
    @else
        <div class="text-red-600 font-semibold">
            🚫 No hay empresas disponibles para llamar ahora mismo.
        </div>
    @endif
</x-filament::page>
