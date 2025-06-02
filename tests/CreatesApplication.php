<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

/**
 * Trait para crear la aplicación dentro de PHPUnit.
 */
trait CreatesApplication
{
    /**
     * Crea la aplicación Laravel para pruebas.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
