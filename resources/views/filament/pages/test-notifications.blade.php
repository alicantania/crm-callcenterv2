<x-filament::page>
    <div class="space-y-6">
        <div class="p-6 bg-white rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Prueba de Notificaciones</h2>
            <p class="mb-4">Esta página permite probar los diferentes tipos de notificaciones:</p>
            
            <ul class="list-disc pl-5 mb-4 space-y-2">
                <li>Toast (notificación voladora que desaparece)</li>
                <li>Persistente (aparece en la campanita)</li>
                <li>Flash (mensaje de sesión)</li>
            </ul>

            <p class="text-sm text-gray-600">Usa los botones de arriba para probar cada tipo.</p>
        </div>

        @if (session('success'))
            <div class="p-4 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="p-6 bg-white rounded-lg shadow">
            <h3 class="text-lg font-medium mb-2">Información</h3>
            <p>Si las notificaciones no funcionan, podemos intentar soluciones alternativas:</p>
            <ul class="list-disc pl-5 mt-2">
                <li>Verificar la instalación del paquete</li>
                <li>Comprobar si livewire está actualizado</li>
                <li>Revisar si hay errores en la consola del navegador</li>
            </ul>
        </div>
    </div>
</x-filament::page>