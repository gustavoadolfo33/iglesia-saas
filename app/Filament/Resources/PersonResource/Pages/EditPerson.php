<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Resources\Pages\EditRecord;

class EditPerson extends EditRecord
{
    protected static string $resource = PersonResource::class;

    protected ?int $selectedMemberId = null;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['linked_member_id'] = PersonResource::getLinkedMemberIdForForm($this->record);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedMemberId = isset($data['linked_member_id']) ? (int) $data['linked_member_id'] : null;
        unset($data['linked_member_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        PersonResource::syncMemberLink($this->record, $this->selectedMemberId);
    }
}
