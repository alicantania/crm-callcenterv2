<x-filament::page>
    <x-filament::section>
        <x-filament::grid columns="2" md="2" gap="8">
            <div>
                <x-filament::section.heading>
                    Datos de la Empresa
                </x-filament::section.heading>
                <dl class="mt-4 space-y-2">
                    <div>
                        <dt class="font-medium text-gray-700">Empresa</dt>
                        <dd class="text-lg">{{ $record->company_name }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">CIF</dt>
                        <dd>{{ $record->cif }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Operador</dt>
                        <dd>{{ optional($record->operator)->name }}</dd>
                    </div>
                </dl>
            </div>
            <div>
                <x-filament::section.heading>
                    Detalles de la Venta
                </x-filament::section.heading>
                <dl class="mt-4 space-y-2">
                    <div>
                        <dt class="font-medium text-gray-700">Número de Contrato</dt>
                        <dd class="text-lg">{{ $record->contract_number }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Estado</dt>
                        <dd>
                            <x-filament::badge color="primary">
                                {{ ucfirst($record->status) }}
                            </x-filament::badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Fecha de venta</dt>
                        <dd>{{ $record->sale_date }}</dd>
                    </div>
                </dl>
            </div>
        </x-filament::grid>
    </x-filament::section>

    <x-filament::section class="mt-8">
        <x-filament::section.heading>
            Seguimiento y Cambios de Estado
        </x-filament::section.heading>
        <div class="mt-6">
            @if($record->saleTrackings->count())
                <ol class="relative border-l border-gray-200">
                    @foreach($record->saleTrackings->sortByDesc('created_at') as $tracking)
                        <li class="mb-10 ml-6">
                            <span class="absolute flex items-center justify-center w-6 h-6 bg-primary-100 rounded-full -left-3 ring-8 ring-white">
                                <x-heroicon-o-arrow-path class="w-4 h-4 text-primary-600" />
                            </span>
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900">
                                    {{ ucfirst($tracking->old_status) }} → <span class="text-primary-700">{{ ucfirst($tracking->new_status) }}</span>
                                </h3>
                                <span class="text-xs text-gray-500">{{ $tracking->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-sm text-gray-700 mt-1">
                                @if($tracking->notes)
                                    <span class="block text-gray-500 italic">Notas: {{ $tracking->notes }}</span>
                                @endif
                                <span class="block mt-1">Por: <span class="font-medium">{{ optional($tracking->user)->name }}</span></span>
                            </p>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="text-gray-500">No hay historial de cambios para esta venta.</p>
            @endif
        </div>
    </x-filament::section>
</x-filament::page>
