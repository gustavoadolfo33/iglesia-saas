<?php

namespace App\Filament\Resources\FormationTrackLeaderResource\Pages;

use App\Filament\Resources\FormationTrackLeaderResource;
use Filament\Resources\Pages\EditRecord;

class EditFormationTrackLeader extends EditRecord
{
    protected static string $resource = FormationTrackLeaderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return FormationTrackLeaderResource::normalizeTeacherAssignment($data, $this->record);
    }
}
