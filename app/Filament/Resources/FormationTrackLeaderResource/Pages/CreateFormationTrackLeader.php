<?php

namespace App\Filament\Resources\FormationTrackLeaderResource\Pages;

use App\Filament\Resources\FormationTrackLeaderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFormationTrackLeader extends CreateRecord
{
    protected static string $resource = FormationTrackLeaderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return FormationTrackLeaderResource::normalizeTeacherAssignment($data);
    }
}
