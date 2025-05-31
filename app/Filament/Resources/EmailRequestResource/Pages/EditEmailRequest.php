<?php

namespace App\Filament\Resources\EmailRequestResource\Pages;

use App\Filament\Resources\EmailRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailRequest extends EditRecord
{
    protected static string $resource = EmailRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
