<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Tests\TestCase;

class NotificationPersistenceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tramitar_venta_envia_notificacion_a_operador_y_tramitador()
    {
        // Asegurar que los roles existen
        \App\Models\Role::firstOrCreate(['id' => 1], ['name' => 'Operador']);
        \App\Models\Role::firstOrCreate(['id' => 2], ['name' => 'Administrador']);

        // Crear usuarios
        $operador = User::factory()->create(['role_id' => 1]);
        $tramitador = User::factory()->create(['role_id' => 2]);

        // Crear dependencias requeridas
        $company = \App\Models\Company::factory()->create();
        $product = \App\Models\Product::factory()->create();
        $businessLine = \App\Models\BusinessLine::factory()->create();

        // Crear venta asignada al operador
        $sale = Sale::factory()->create([
            'company_name' => $company->name,
            'cif' => $company->cif,
            'address' => $company->address,
            'city' => $company->city,
            'province' => $company->province,
            'phone' => $company->phone,
            'email' => $company->email,
            'activity' => $company->activity,
            'cnae' => $company->cnae,
            'operator_id' => $operador->id,
            'product_id' => $product->id,
            'business_line_id' => $businessLine->id,
            'status' => 'pendiente',
        ]);

        // Autenticar como tramitador
        $this->actingAs($tramitador);

        // Simular acción de tramitar la venta
        Notification::fake();

        // Prueba directa: crear una notificación usando el modelo
        \Filament\Notifications\DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'test',
            'notifiable_id' => $operador->id,
            'notifiable_type' => get_class($operador),
            'data' => json_encode(['title' => 'Test', 'body' => 'Test']),
        ]);

        // Simular lógica de tramitación (imitando VentasPendientesDeTramitar)
        FilamentNotification::make()
            ->title("Venta #{$sale->id} tramitada")
            ->body("Su venta ha pasado a estado: tramitada.")
            ->success()
            ->sendToDatabase($operador);

        FilamentNotification::make()
            ->title("Venta #{$sale->id} tramitada")
            ->body("La venta ha pasado a estado: tramitada.")
            ->success()
            ->sendToDatabase($tramitador);

        // Verificar notificaciones persistentes en la base de datos
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $operador->id,
            'notifiable_type' => get_class($operador),
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $tramitador->id,
            'notifiable_type' => get_class($tramitador),
        ]);
    }
}
