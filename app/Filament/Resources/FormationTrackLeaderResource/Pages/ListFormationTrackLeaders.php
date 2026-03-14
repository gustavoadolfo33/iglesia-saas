<?php

namespace App\Filament\Resources\FormationTrackLeaderResource\Pages;

use App\Filament\Resources\FormationTrackLeaderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFormationTrackLeaders extends ListRecords
{
    protected static string $resource = FormationTrackLeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
