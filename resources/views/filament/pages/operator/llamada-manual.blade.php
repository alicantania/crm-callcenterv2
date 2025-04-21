<x-filament::page>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold">Llamada Manual</h2>

        @if ($empresa)
            <div class="p-6 bg-white shadow rounded space-y-4">
                <div class="text-lg font-semibold">📞 Empresa seleccionada</div>

                <div><strong>Nombre:</strong> {{ $empresa->name }}</div>
                <div><strong>Teléfono:</strong> {{ $empresa->phone }}</div>
                <div><strong>Email:</strong> {{ $empresa->email }}</div>
                <div><strong>Dirección:</strong> {{ $empresa->address }}</div>
                <div><strong>Actividad:</strong> {{ $empresa->activity }}</div>
                <div><strong>CNAE:</strong> {{ $empresa->cnae }}</div>

                <hr class="my-4" />

                <form wire:submit.prevent="guardarResultado">
                    {{-- Resultado de llamada --}}
                    <div class="space-y-4">
                        <div>
                            <label for="resultado" class="block font-medium">Resultado de la llamada</label>
                            <select wire:model.defer="resultado" id="resultado" class="w-full rounded border-gray-300">
                                <option value="">Selecciona una opción</option>
                                <option value="interesado">Interesado</option>
                                <option value="no_contesta">No contesta</option>
                                <option value="volver_a_llamar">Volver a llamar</option>
                            </select>
                        </div>

                        {{-- Campo extra si quiere volver a llamar --}}
                        @if ($resultado === 'volver_a_llamar')
                            <div>
                                <label for="fecha_rellamada" class="block font-medium">¿Cuándo volver a llamar?</label>
                                <input type="datetime-local" wire:model.defer="fecha_rellamada" id="fecha_rellamada" class="w-full rounded border-gray-300">
                            </div>
                        @endif

                        {{-- Comentarios --}}
                        <div>
                            <label for="comentarios" class="block font-medium">Comentarios</label>
                            <textarea wire:model.defer="comentarios" id="comentarios" rows="4" class="w-full rounded border-gray-300"></textarea>
                        </div>

                        {{-- Persona de contacto --}}
                        <div>
                            <label for="contacto" class="block font-medium">Persona de contacto</label>
                            <input type="text" wire:model.defer="contacto" id="contacto" class="w-full rounded border-gray-300">
                        </div>

                        {{-- Botón para registrar --}}
                        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded">
                            Guardar resultado
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="text-red-600 font-semibold">No hay empresas disponibles para llamar ahora mismo.</div>
        @endif
    </div>
</x-filament::page>
