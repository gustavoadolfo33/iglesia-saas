<?php

namespace App\Filament\Resources\PersonFormationResource\Pages;

use App\Filament\Resources\PersonFormationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePersonFormation extends CreateRecord
{
    protected static string $resource = PersonFormationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return PersonFormationResource::normalizeFormationData($data);
    }
}
