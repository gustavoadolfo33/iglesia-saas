<?php

namespace App\Filament\Resources\DiscipleshipTrackResource\Pages;

use App\Filament\Resources\DiscipleshipTrackResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiscipleshipTracks extends ListRecords
{
    protected static string $resource = DiscipleshipTrackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
