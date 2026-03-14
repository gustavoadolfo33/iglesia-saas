<?php

namespace App\Filament\Resources\FormationTrackResource\Pages;

use App\Filament\Resources\FormationTrackResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFormationTracks extends ListRecords
{
    protected static string $resource = FormationTrackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
