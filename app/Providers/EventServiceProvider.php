<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\ServiceProvider;
use App\Services\ActivityLogService;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registrar eventos de autenticación
        $this->registerAuthEvents();
    }
    
    /**
     * Registrar eventos de autenticación para el logging
     */
    protected function registerAuthEvents(): void
    {
        // Evento de inicio de sesión
        $this->app['events']->listen(Login::class, function ($event) {
            ActivityLogService::logLogin();
        });
        
        // Evento de cierre de sesión
        $this->app['events']->listen(Logout::class, function ($event) {
            ActivityLogService::logLogout();
        });
    }
}
