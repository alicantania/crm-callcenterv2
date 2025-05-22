<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class RoleHelper
{
    /**
     * Verifica si el usuario autenticado tiene alguno de los roles indicados.
     */
    public static function userHasRole(array $roles): bool
    {
        if (Auth::check() && Auth::user()?->role?->name === 'Superadmin') {
            return true;
        }
        return Auth::check() && in_array(Auth::user()?->role?->name, $roles);
    }

    /**
     * Verifica si el usuario autenticado NO tiene ninguno de los roles indicados.
     */
    public static function userHasNotRole(array $roles): bool
    {
        if (Auth::check() && Auth::user()?->role?->name === 'Superadmin') {
            return true;
        }
        return Auth::check() && !in_array(Auth::user()?->role?->name, $roles);
    }
}
