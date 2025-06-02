<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard (ruta stub para tests)
Route::get('dashboard', function () {
    return '';
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    
});

require __DIR__.'/auth.php';

// ✅ RUTA DE PRUEBA PARA VER NOTIFICACIONES
use Filament\Notifications\Notification;

Route::get('/test-filament-notification', function () {
    Notification::make()
        ->title('Notificación de prueba')
        ->body('Esta es una notificación de prueba simple.')
        ->success()
        ->send();
        
    return redirect()->to('/admin')->with('message', 'Notificación enviada, deberías verla en el panel');
});