<?php

namespace App\Filament\Resources\EmailRequestResource\Pages;

use App\Filament\Resources\EmailRequestResource;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use App\Models\EmailRequest;
use App\Helpers\RoleHelper;

class ViewEmailRequest extends Page
{
    protected static string $resource = EmailRequestResource::class;

    protected static string $view = 'filament.resources.email-request-resource.pages.view-email-request';
    
    public ?EmailRequest $record = null;
    
    public function mount($record): void
    {
        // Ensure record is an ID, not an object
        if (is_object($record) || is_array($record)) {
            $record = $record['id'] ?? null;
            if (!$record) {
                abort(404);
            }
        }

        $this->record = EmailRequest::findOrFail($record);
        
        // Ensure only the operator who created the request or admins can view it
        if (auth()->user()->role->name === 'Operador' && auth()->id() !== $this->record->requested_by_id) {
            abort(403);
        }
        
        // Marcar notificación como vista cuando operador ve solicitud procesada
        if (RoleHelper::userHasRole(['Operador']) && $this->record->status === 'processed' && !$this->record->operator_seen) {
            $this->record->operator_seen = true;
            $this->record->save();
        }
    }

    protected function getHeaderActions(): array
    {
        // Only allow viewing
        return [];
    }

    protected function getFormSchema(): array
    {
        $record = $this->record;
        return [
            Section::make('Notas')
                ->schema([
                    Placeholder::make('notes')
                        ->label('Notas del operador')
                        ->content($record->notes ?? '-'),
                    Placeholder::make('admin_notes')
                        ->label('Notas del administrador')
                        ->content($record->admin_notes ?? '-'),
                ]),
        ];
    }
}
