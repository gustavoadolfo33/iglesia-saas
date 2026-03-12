<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePerson extends CreateRecord
{
    protected static string $resource = PersonResource::class;

    protected ?int $selectedMemberId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedMemberId = isset($data['linked_member_id']) ? (int) $data['linked_member_id'] : null;
        unset($data['linked_member_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        PersonResource::syncMemberLink($this->record, $this->selectedMemberId);
    }
}
