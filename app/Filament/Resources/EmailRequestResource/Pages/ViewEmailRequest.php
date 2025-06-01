<?php

namespace App\Filament\Resources\EmailRequestResource\Pages;

use App\Filament\Resources\EmailRequestResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;

class ViewEmailRequest extends ViewRecord
{
    protected static string $resource = EmailRequestResource::class;

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
