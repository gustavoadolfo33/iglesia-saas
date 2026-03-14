<?php

namespace App\Filament\Resources\PersonFormationResource\Pages;

use App\Filament\Resources\PersonFormationResource;
use Filament\Resources\Pages\EditRecord;

class EditPersonFormation extends EditRecord
{
    protected static string $resource = PersonFormationResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PersonFormationResource::normalizeFormationData($data, $this->record);
    }
}
