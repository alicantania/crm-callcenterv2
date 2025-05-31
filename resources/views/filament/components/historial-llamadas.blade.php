<div class="bg-white rounded-lg shadow p-4 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">📞 Historial de Interacciones</h3>
    
    @if(isset($empresa) && $empresa->calls->count() > 0)
        <div class="space-y-4">
            @foreach($empresa->calls()->with('user')->latest('call_date')->take(10)->get() as $call)
                @php
                    // Definir colores y etiquetas directamente
                    $borderColor = match($call->status) {
                        'no_interesa' => 'border-red-500',
                        'no_contesta' => 'border-yellow-500',
                        'volver_a_llamar' => 'border-blue-500',
                        'contacto' => 'border-green-500',
                        'error' => 'border-gray-500',
                        default => 'border-gray-300'
                    };
                    
                    $statusLabel = match($call->status) {
                        'no_interesa' => '❌ No interesa',
                        'no_contesta' => '⌛ No contesta',
                        'volver_a_llamar' => '🔄 Volver a llamar',
                        'contacto' => '✅ Contacto',
                        'error' => '⚠️ Error',
                        default => ucfirst($call->status)
                    };
                @endphp
                
                <div class="border-l-4 {{ $borderColor }} pl-4 py-2">
                    <div class="flex justify-between">
                        <span class="font-semibold">{{ \Carbon\Carbon::parse($call->call_date)->format('d/m/Y H:i') }}</span>
                        <span class="text-gray-500 text-sm">{{ $call->user->name ?? 'Operador' }}</span>
                    </div>
                    <div class="mt-1">
                        <span class="bg-gray-100 text-xs font-medium px-2 py-1 rounded">
                            {{ $statusLabel }}
                        </span>
                        @if($call->recall_at)
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium ml-2 px-2 py-1 rounded">
                                📅 Rellamada: {{ \Carbon\Carbon::parse($call->recall_at)->format('d/m/Y H:i') }}
                            </span>
                        @endif
                    </div>
                    @if($call->contact_person)
                        <div class="mt-1 text-sm">
                            <span class="font-medium">👤 Contacto:</span> {{ $call->contact_person }}
                        </div>
                    @endif
                    @if($call->notes)
                        <div class="mt-1 text-sm text-gray-700">
                            {{ $call->notes }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        
        @if($empresa->calls->count() > 10)
            <div class="mt-4 text-center">
                <a href="#" onclick="event.preventDefault(); alert('Funcionalidad en desarrollo');" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                    Ver historial completo ({{ $empresa->calls->count() }} interacciones)
                </a>
            </div>
        @endif
    @else
        <div class="text-gray-500 py-3 text-center">
            No hay historial de interacciones para esta empresa.
        </div>
    @endif
    
    <!-- Sección para mostrar cursos de interés -->
    @if(isset($empresa) && !empty($empresa->curso_interesado))
        <div class="mt-4 pt-4 border-t border-gray-200">
            <h4 class="font-medium text-gray-900 mb-2">🎓 Cursos de Interés</h4>
            <div class="border-l-4 border-green-500 pl-4 py-2">
                <div class="flex justify-between">
                    <span class="font-semibold">{{ \App\Models\Product::find($empresa->curso_interesado)?->name ?? 'Curso' }}</span>
                    <span class="text-gray-500 text-sm">{{ $empresa->fecha_interes ? \Carbon\Carbon::parse($empresa->fecha_interes)->format('d/m/Y') : 'Fecha no especificada' }}</span>
                </div>
                @if(!empty($empresa->modalidad_interesada))
                    <div class="mt-1 text-sm">
                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded">
                            {{ ucfirst($empresa->modalidad_interesada) }}
                        </span>
                    </div>
                @endif
                @if(!empty($empresa->observaciones_interes))
                    <div class="mt-1 text-sm text-gray-700">
                        {{ $empresa->observaciones_interes }}
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <!-- Sección para mostrar solicitudes de email -->
    @if(isset($empresa) && method_exists($empresa, 'emailRequests') && $empresa->emailRequests()->count() > 0)
        <div class="mt-4 pt-4 border-t border-gray-200">
            <h4 class="font-medium text-gray-900 mb-2">📧 Solicitudes de Email</h4>
            <div class="space-y-4">
                @foreach($empresa->emailRequests()->latest()->take(5)->get() as $request)
                    @php
                    // Definir colores y etiquetas directamente para emails
                    $emailBorderColor = match($request->status) {
                        'pending' => 'border-yellow-500',
                        'processed' => 'border-green-500',
                        'cancelled' => 'border-red-500',
                        default => 'border-gray-300'
                    };
                    
                    $emailStatusLabel = match($request->status) {
                        'pending' => '⌛ Pendiente',
                        'processed' => '✅ Procesado',
                        'cancelled' => '❌ Cancelado',
                        default => ucfirst($request->status)
                    };
                    @endphp
                    
                    <div class="border-l-4 {{ $emailBorderColor }} pl-4 py-2">
                        <div class="flex justify-between">
                            <span class="font-semibold">{{ \Carbon\Carbon::parse($request->created_at)->format('d/m/Y H:i') }}</span>
                            <span class="text-gray-500 text-sm">{{ \App\Models\User::find($request->requested_by_id)->name ?? 'Operador' }}</span>
                        </div>
                        <div class="mt-1">
                            <span class="bg-gray-100 text-xs font-medium px-2 py-1 rounded">
                                {{ $emailStatusLabel }}
                            </span>
                        </div>
                        <div class="mt-1 text-sm">
                            <span class="font-medium">📚 Curso:</span> {{ \App\Models\Product::find($request->product_id)?->name ?? 'Curso no especificado' }}
                        </div>
                        <div class="mt-1 text-sm">
                            <span class="font-medium">📧 Destinatario:</span> {{ $request->email_to }}
                        </div>
                        @if($request->notes)
                            <div class="mt-1 text-sm text-gray-700">
                                {{ $request->notes }}
                            </div>
                        @endif
                        @if($request->admin_notes && $request->status === 'processed')
                            <div class="mt-1 text-sm bg-blue-50 p-2 rounded">
                                <span class="font-medium">📝 Notas del administrador:</span> {{ $request->admin_notes }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- Ya no necesitamos definir funciones auxiliares aquí --}}
