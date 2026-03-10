<?php

namespace App\Filament\Resources\MeetingGroupResource\Pages;

use App\Filament\Resources\MeetingGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeetingGroup extends EditRecord
{
    protected static string $resource = MeetingGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
