<x-filament::page>
    <div class="space-y-6">
        
        <!-- Descripción de la página -->
        <div class="text-gray-600">
            Aquí encontrarás todas las empresas con las que has interactuado y que están marcadas para seguimiento o han sido contactadas.
            Puedes ver su historial de llamadas y realizar nuevas llamadas.
        </div>

        <!-- Tabla de contactos -->
        {{ $this->table }}
    </div>
</x-filament::page>
