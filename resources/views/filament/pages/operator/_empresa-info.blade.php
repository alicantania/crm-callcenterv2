<div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded shadow">
    <div><strong>📛 Empresa:</strong> {{ $empresa->name }}</div>
    <div><strong>📍 Dirección:</strong> {{ $empresa->address }}</div>
    <div><strong>🏙️ Ciudad:</strong> {{ $empresa->city }}</div>
    <div><strong>🌍 Provincia:</strong> {{ $empresa->province }}</div>
    <div><strong>📞 Teléfono:</strong> {{ $empresa->phone }}</div>
    <div><strong>✉️ Email:</strong> {{ $empresa->email }}</div>
    <div><strong>🏢 Actividad:</strong> {{ $empresa->activity }}</div>
    <div><strong>🔢 CNAE:</strong> {{ $empresa->cnae }}</div>
</div>
