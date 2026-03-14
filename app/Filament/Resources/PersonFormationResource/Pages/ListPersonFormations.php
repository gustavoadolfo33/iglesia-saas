<?php

namespace App\Filament\Resources\PersonFormationResource\Pages;

use App\Filament\Resources\PersonFormationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPersonFormations extends ListRecords
{
    protected static string $resource = PersonFormationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
