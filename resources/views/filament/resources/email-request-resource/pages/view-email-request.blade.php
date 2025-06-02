@php
    // Simple read-only view for email requests
    $record = $this->record;
@endphp

<x-filament-panels::page>
    <x-filament::section>
        <h2 class="text-xl font-bold mb-4">Información de la solicitud</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <h3 class="font-medium text-gray-500">Empresa</h3>
                <p class="mt-1">{{ $record->company->name }}</p>
            </div>
            
            <div>
                <h3 class="font-medium text-gray-500">Curso/Producto</h3>
                <p class="mt-1">{{ $record->product->name }}</p>
            </div>
            
            <div>
                <h3 class="font-medium text-gray-500">Email de destino</h3>
                <p class="mt-1">{{ $record->email_to }}</p>
            </div>
            
            <div>
                <h3 class="font-medium text-gray-500">Persona de contacto</h3>
                <p class="mt-1">{{ $record->contact_person }}</p>
            </div>
            
            <div>
                <h3 class="font-medium text-gray-500">Solicitado por</h3>
                <p class="mt-1">{{ $record->requestedBy->name }}</p>
            </div>
            
            <div>
                <h3 class="font-medium text-gray-500">Estado</h3>
                <p class="mt-1">
                    @if($record->status === 'pending')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pendiente</span>
                    @elseif($record->status === 'processed')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Procesado</span>
                    @elseif($record->status === 'cancelled')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Cancelado</span>
                    @endif
                </p>
            </div>
        </div>
    </x-filament::section>
    
    <x-filament::section class="mt-6">
        <h2 class="text-xl font-bold mb-4">Notas del operador</h2>
        <div class="p-4 bg-gray-50 rounded-lg">
            {!! nl2br(e($record->notes ?? 'No hay notas del operador')) !!}
        </div>
    </x-filament::section>
    
    <x-filament::section class="mt-6">
        <h2 class="text-xl font-bold mb-4">Notas del administrador</h2>
        <div class="p-4 bg-gray-50 rounded-lg">
            {!! nl2br(e($record->admin_notes ?? 'No hay notas del administrador')) !!}
        </div>
    </x-filament::section>
    
    <x-filament::section class="mt-6">
        <h2 class="text-xl font-bold mb-4">Información de procesamiento</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h3 class="font-medium text-gray-500">Procesado por</h3>
                <p class="mt-1 font-bold {{ $record->processedBy ? 'text-gray-900' : 'text-gray-400 italic' }}">
                    {{ $record->processedBy?->name ?? 'No procesado aún' }}
                </p>
            </div>
            
            <div>
                <h3 class="font-medium text-gray-500">Fecha de procesamiento</h3>
                <p class="mt-1 font-bold {{ $record->processed_at ? 'text-gray-900' : 'text-gray-400 italic' }}">
                    {{ $record->processed_at ? $record->processed_at->format('d/m/Y H:i') : 'No procesado aún' }}
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
