<div class="space-y-6">
    <h2 class="text-2xl font-bold">Llamada Manual</h2>

    @if ($empresa)
        <div class="p-6 bg-white shadow rounded space-y-4">
            <div class="text-lg font-semibold">📞 Empresa seleccionada</div>

            <div><strong>Nombre:</strong> {{ $empresa->name }}</div>
            <div><strong>Teléfono:</strong> {{ $empresa->phone }}</div>
            <div><strong>Email:</strong> {{ $empresa->email }}</div>
            <div><strong>Dirección:</strong> {{ $empresa->address }}</div>
            <div><strong>Localidad:</strong> {{ $empresa->city }}</div>
            <div><strong>Provincia:</strong> {{ $empresa->province }}</div>
            <div><strong>Actividad:</strong> {{ $empresa->activity }}</div>
            <div><strong>CNAE:</strong> {{ $empresa->cnae }}</div>

            <hr class="my-4" />

            {{-- Mensajes flash --}}
            @if (session()->has('message'))
                <div class="p-3 text-green-800 bg-green-100 rounded">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="p-3 text-red-800 bg-red-100 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Formulario --}}
            <form wire:submit.prevent="guardarResultado" class="space-y-5 mb-10">
                {{-- Resultado --}}
                <div>
                    <label class="block font-medium">Resultado de la llamada</label>
                    <select wire:model="resultado" class="w-full rounded border-gray-300">
                        <option value="">Selecciona una opción</option>
                        <option value="no_interesa">No interesa</option>
                        <option value="no_contesta">No contesta</option>
                        <option value="volver_a_llamar">Volver a llamar</option>
                        <option value="contacto">Contacto</option>
                        <option value="venta">Venta</option>
                        <option value="error">Error</option>
                    </select>
                </div>

                {{-- Fecha para volver a llamar --}}
                @if (in_array($resultado, ['volver_a_llamar', 'contacto']))
                    <div>
                        <label class="block font-medium">¿Cuándo volver a llamar?</label>
                        <input type="datetime-local" wire:model="fecha_rellamada" class="w-full rounded border-gray-300">
                    </div>
                @endif

                {{-- Comentarios --}}
                <div>
                    <label class="block font-medium">Comentarios</label>
                    <textarea wire:model.defer="comentarios" rows="3" class="w-full rounded border-gray-300"></textarea>
                </div>

                {{-- Persona de contacto --}}
                <div>
                    <label class="block font-medium">Persona de contacto</label>
                    <input type="text" wire:model.defer="contacto" class="w-full rounded border-gray-300">
                </div>

                {{-- Botón Guardar --}}
                <div>
                    <x-filament::button type="submit" color="primary" size="xl" class="w-full justify-center">
                        🔥 Guardar resultado 🔥
                    </x-filament::button>
                </div>

            </form>
        </div>
    @else
        <div class="text-red-600 font-semibold">No hay empresas disponibles para llamar ahora mismo.</div>
    @endif
</div>
