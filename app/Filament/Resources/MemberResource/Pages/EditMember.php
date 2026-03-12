<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $canonicalPersonId = $this->record->person?->id ?? $this->record->legacyPerson?->id;

        if (!$data['person_id'] && $canonicalPersonId) {
            $data['person_id'] = $canonicalPersonId;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        MemberResource::syncPersonLink($this->record, $this->record->person_id);
    }
}
