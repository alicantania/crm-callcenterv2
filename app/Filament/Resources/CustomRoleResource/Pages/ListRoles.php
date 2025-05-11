<?php

namespace App\Filament\Resources\CustomRoleResource\Pages;

use App\Filament\Resources\CustomRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = CustomRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
