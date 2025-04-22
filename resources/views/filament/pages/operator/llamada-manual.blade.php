<x-filament::page>
    @if ($empresa)
        <div class="space-y-6">
            <div class="text-xl font-bold text-gray-800">
                Empresa seleccionada: {{ $empresa->name }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded shadow">
                <div><strong>📍 Dirección:</strong> {{ $empresa->address }}</div>
                <div><strong>🏙️ Ciudad:</strong> {{ $empresa->city }}</div>
                <div><strong>🌍 Provincia:</strong> {{ $empresa->province }}</div>
                <div><strong>📞 Teléfono:</strong> {{ $empresa->phone }}</div>
                <div><strong>✉️ Email:</strong> {{ $empresa->email }}</div>
                <div><strong>🏢 Actividad:</strong> {{ $empresa->activity }}</div>
                <div><strong>🔢 CNAE:</strong> {{ $empresa->cnae }}</div>
            </div>

            <x-filament::form :form="$this->form" wire:submit="submit">
                <x-filament::button type="submit" color="success" class="mt-6 w-full text-lg py-3">
                    ✅ Guardar resultado de la llamada
                </x-filament::button>
            </x-filament::form>
        </div>
    @else
        <div class="text-red-600 font-semibold">🚫 No hay empresas disponibles para llamar ahora mismo.</div>
    @endif
</x-filament::page>
