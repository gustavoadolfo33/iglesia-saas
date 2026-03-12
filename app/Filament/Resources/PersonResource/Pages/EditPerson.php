<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Resources\Pages\EditRecord;

class EditPerson extends EditRecord
{
    protected static string $resource = PersonResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $canonicalMemberId = $this->record->member?->id;

        if (!$data['member_id'] && $canonicalMemberId) {
            $data['member_id'] = $canonicalMemberId;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        PersonResource::syncMemberLink($this->record, $this->record->member_id);
    }
}
