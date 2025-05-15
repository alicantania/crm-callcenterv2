<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Facades\Auth;
use App\Notifications\FilamentPersistentNotification;

class TestNotifications extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static string $view = 'filament.pages.test-notifications';
    protected static ?string $title = 'Probar Notificaciones';
    protected static ?string $navigationLabel = 'Probar Notificaciones';

    protected function getActions(): array
    {
        return [
            Action::make('testNotification')
                ->label('Enviar notificación de prueba')
                ->size(ActionSize::Large)
                ->icon('heroicon-m-bell')
                ->color('success')
                ->action(function (): void {
                    // Toast (voladora)
                    Notification::make()
                        ->title('Notificación de prueba (toast)')
                        ->body('Esta es una notificación toast desde la acción.')
                        ->success()
                        ->send();

                    // Persistente (campanita)
                    if (Auth::check()) {
                        Auth::user()->notify(new FilamentPersistentNotification());
                    }
                }),
            
            Action::make('testFlashNotification')
                ->label('Enviar notificación con Flash')
                ->size(ActionSize::Large)
                ->icon('heroicon-m-information-circle')
                ->color('info')
                ->action(function (): void {
                    session()->flash('success', 'Esta es una notificación flashcon code.');
                    $this->redirect(TestNotifications::getUrl());
                })
        ];
    }
}
