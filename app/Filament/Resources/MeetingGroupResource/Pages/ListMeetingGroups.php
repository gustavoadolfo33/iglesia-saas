<?php

namespace App\Filament\Resources\MeetingGroupResource\Pages;

use App\Filament\Resources\MeetingGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMeetingGroups extends ListRecords
{
    protected static string $resource = MeetingGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
