<?php

namespace App\Filament\Resources\EmailRequestResource\Pages;

use App\Filament\Resources\EmailRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailRequests extends ListRecords
{
    protected static string $resource = EmailRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Create action removed - operators should create email requests from company page
        ];
    }
}
