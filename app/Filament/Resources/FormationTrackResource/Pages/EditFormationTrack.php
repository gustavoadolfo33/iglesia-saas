<?php

namespace App\Filament\Resources\FormationTrackResource\Pages;

use App\Filament\Resources\FormationTrackResource;
use App\Models\FormationTrack;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditFormationTrack extends EditRecord
{
    protected static string $resource = FormationTrackResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['scope_type'] ?? null) !== 'church') {
            $data['church_id'] = null;
        } elseif (empty($data['church_id'])) {
            $data['church_id'] = auth()->user()?->current_church_id;
        }

        if (!($data['is_paid'] ?? false)) {
            $data['price'] = null;
            $data['currency'] = null;
        }

        $exists = FormationTrack::query()
            ->where('slug', $data['slug'])
            ->where('church_id', $data['church_id'] ?? null)
            ->whereKeyNot($this->record->getKey())
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'slug' => 'Ya existe una ruta con este slug en el mismo alcance de iglesia.',
            ]);
        }

        return $data;
    }
}
