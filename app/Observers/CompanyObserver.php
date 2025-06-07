<?php

namespace App\Observers;

use App\Models\Company;
use App\Services\ActivityLogService;

class CompanyObserver
{
    /**
     * Handle the Company "created" event.
     */
    public function created(Company $company): void
    {
        ActivityLogService::logCreated($company, "Se ha creado una nueva empresa: {$company->name}");
    }

    /**
     * Handle the Company "updated" event.
     */
    public function updated(Company $company): void
    {
        $changes = $company->getChanges();
        
        // Registrar específicamente cambios en assigned_operator_id
        if (isset($changes['assigned_operator_id'])) {
            $oldOperatorId = $company->getOriginal('assigned_operator_id');
            $newOperatorId = $changes['assigned_operator_id'];
            
            if ($newOperatorId && !$oldOperatorId) {
                ActivityLogService::log(
                    'company_assigned',
                    "Empresa {$company->name} asignada a operador ID: {$newOperatorId}",
                    $company,
                    [
                        'operator_id' => $newOperatorId,
                        'company_id' => $company->id,
                    ]
                );
            } elseif (!$newOperatorId && $oldOperatorId) {
                ActivityLogService::log(
                    'company_unassigned',
                    "Empresa {$company->name} liberada del operador ID: {$oldOperatorId}",
                    $company,
                    [
                        'previous_operator_id' => $oldOperatorId,
                        'company_id' => $company->id,
                    ]
                );
            } elseif ($newOperatorId !== $oldOperatorId) {
                ActivityLogService::log(
                    'company_reassigned',
                    "Empresa {$company->name} reasignada de operador ID: {$oldOperatorId} a operador ID: {$newOperatorId}",
                    $company,
                    [
                        'previous_operator_id' => $oldOperatorId,
                        'new_operator_id' => $newOperatorId,
                        'company_id' => $company->id,
                    ]
                );
            }
        }
        
        // Registrar cambios en locked_to_operator
        if (isset($changes['locked_to_operator'])) {
            $action = $changes['locked_to_operator'] ? 'company_locked' : 'company_unlocked';
            $message = $changes['locked_to_operator'] 
                ? "Empresa {$company->name} bloqueada para el operador ID: {$company->assigned_operator_id}"
                : "Empresa {$company->name} desbloqueada";
                
            ActivityLogService::log(
                $action,
                $message,
                $company,
                [
                    'operator_id' => $company->assigned_operator_id,
                    'company_id' => $company->id,
                ]
            );
        }
        
        ActivityLogService::logUpdated($company, $changes, "Se ha actualizado la empresa: {$company->name}");
    }

    /**
     * Handle the Company "deleted" event.
     */
    public function deleted(Company $company): void
    {
        ActivityLogService::logDeleted($company, "Se ha eliminado la empresa: {$company->name}");
    }

    /**
     * Handle the Company "restored" event.
     */
    public function restored(Company $company): void
    {
        ActivityLogService::log(
            'restore',
            "Se ha restaurado la empresa: {$company->name}",
            $company
        );
    }

    /**
     * Handle the Company "force deleted" event.
     */
    public function forceDeleted(Company $company): void
    {
        ActivityLogService::log(
            'force_delete',
            "Se ha eliminado permanentemente la empresa: {$company->name}",
            $company
        );
    }
}
