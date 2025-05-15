<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Faker\Generator;
use Faker\Factory as FakerFactory;
use Faker\Provider\Base;
use App\Models\Sale;
use App\Observers\SaleObserver;
use Illuminate\Support\Facades\Blade;
use Filament\Notifications\Livewire\NotificationsComponent;
use Livewire\Livewire;
use App\Livewire\CustomNotificationsComponent;
use App\Notifications\EmpresasLiberadasNotification;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Definimos Faker personalizado con locale español
        $this->app->singleton(Generator::class, function () {
            $faker = FakerFactory::create('es_ES');

            // Añadimos un método personalizado para generar DNIs válidos
            $faker->addProvider(new class($faker) extends Base {
                public function dni(): string
                {
                    $numbers = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
                    $letters = "TRWAGMYFPDXBNJZSQVHLCKE";
                    $letter = $letters[(int) $numbers % 23];
                    return $numbers . $letter;
                }
            });

            return $faker;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registra manualmente el componente de notificaciones de Filament
        //Blade::component('filament-notifications::components.notifications', 'filament-notifications::notifications');
        //Livewire::component('filament.notifications.notifications', CustomNotificationsComponent::class);
    }
}
