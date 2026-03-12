<?php

namespace App\Filament\Resources\PersonDiscipleshipResource\Pages;

use App\Filament\Resources\PersonDiscipleshipResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPersonDiscipleships extends ListRecords
{
    protected static string $resource = PersonDiscipleshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
