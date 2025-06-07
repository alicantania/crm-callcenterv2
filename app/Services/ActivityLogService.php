<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    /**
     * Registrar una acción de usuario en el sistema
     *
     * @param string $action Tipo de acción (login, logout, create, update, delete, etc.)
     * @param string|null $description Descripción detallada de la acción
     * @param Model|null $model Modelo relacionado con la acción
     * @param array $properties Propiedades adicionales para registrar
     * @return ActivityLog
     */
    public static function log(
        string $action,
        ?string $description = null,
        ?Model $model = null,
        array $properties = []
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'properties' => $properties,
        ]);
    }

    /**
     * Registrar inicio de sesión
     */
    public static function logLogin(): void
    {
        if (Auth::check()) {
            self::log(
                'login',
                'Usuario ha iniciado sesión en el sistema',
                Auth::user(),
                [
                    'timestamp' => now()->toDateTimeString(),
                    'ip' => request()->ip(),
                    'browser' => self::getBrowserInfo(),
                    'device' => self::getDeviceInfo(),
                    'os' => self::getOSInfo(),
                ]
            );
        }
    }

    /**
     * Registrar cierre de sesión
     */
    public static function logLogout(): void
    {
        if (Auth::check()) {
            self::log(
                'logout',
                'Usuario ha cerrado sesión en el sistema',
                Auth::user(),
                [
                    'timestamp' => now()->toDateTimeString(),
                    'ip' => request()->ip(),
                ]
            );
        }
    }

    /**
     * Registrar creación de modelo
     */
    public static function logCreated(Model $model, string $description = null): void
    {
        $modelName = class_basename($model);
        self::log(
            'create',
            $description ?? "Usuario ha creado un nuevo registro de {$modelName}",
            $model,
            [
                'id' => $model->getKey(),
                'type' => $modelName,
            ]
        );
    }

    /**
     * Registrar actualización de modelo
     */
    public static function logUpdated(Model $model, array $changed = [], string $description = null): void
    {
        $modelName = class_basename($model);
        self::log(
            'update',
            $description ?? "Usuario ha actualizado un registro de {$modelName}",
            $model,
            [
                'id' => $model->getKey(),
                'type' => $modelName,
                'changed' => $changed,
            ]
        );
    }

    /**
     * Registrar eliminación de modelo
     */
    public static function logDeleted(Model $model, string $description = null): void
    {
        $modelName = class_basename($model);
        self::log(
            'delete',
            $description ?? "Usuario ha eliminado un registro de {$modelName}",
            $model,
            [
                'id' => $model->getKey(),
                'type' => $modelName,
            ]
        );
    }

    /**
     * Obtener información del navegador
     */
    private static function getBrowserInfo(): string
    {
        $userAgent = request()->userAgent();
        $browserInfo = 'Desconocido';
        
        if (strpos($userAgent, 'Firefox') !== false) {
            $browserInfo = 'Firefox';
        } elseif (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) {
            $browserInfo = 'Chrome';
        } elseif (strpos($userAgent, 'Edg') !== false) {
            $browserInfo = 'Edge';
        } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
            $browserInfo = 'Safari';
        } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) {
            $browserInfo = 'Internet Explorer';
        } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
            $browserInfo = 'Opera';
        }
        
        return $browserInfo;
    }

    /**
     * Obtener información del dispositivo
     */
    private static function getDeviceInfo(): string
    {
        $userAgent = request()->userAgent();
        $deviceInfo = 'Desktop';
        
        if (strpos($userAgent, 'iPhone') !== false) {
            $deviceInfo = 'iPhone';
        } elseif (strpos($userAgent, 'iPad') !== false) {
            $deviceInfo = 'iPad';
        } elseif (strpos($userAgent, 'Android') !== false) {
            if (strpos($userAgent, 'Mobile') !== false) {
                $deviceInfo = 'Android Mobile';
            } else {
                $deviceInfo = 'Android Tablet';
            }
        } elseif (strpos($userAgent, 'Windows Phone') !== false) {
            $deviceInfo = 'Windows Phone';
        }
        
        return $deviceInfo;
    }

    /**
     * Obtener información del sistema operativo
     */
    private static function getOSInfo(): string
    {
        $userAgent = request()->userAgent();
        $osInfo = 'Desconocido';
        
        if (strpos($userAgent, 'Windows NT 10.0') !== false) {
            $osInfo = 'Windows 10';
        } elseif (strpos($userAgent, 'Windows NT 6.3') !== false) {
            $osInfo = 'Windows 8.1';
        } elseif (strpos($userAgent, 'Windows NT 6.2') !== false) {
            $osInfo = 'Windows 8';
        } elseif (strpos($userAgent, 'Windows NT 6.1') !== false) {
            $osInfo = 'Windows 7';
        } elseif (strpos($userAgent, 'Mac OS X') !== false) {
            $osInfo = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $osInfo = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $osInfo = 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false || strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            $osInfo = 'iOS';
        }
        
        return $osInfo;
    }
}
