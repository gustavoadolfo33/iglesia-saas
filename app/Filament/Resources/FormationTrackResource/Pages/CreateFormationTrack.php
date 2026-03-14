<?php

namespace App\Filament\Resources\FormationTrackResource\Pages;

use App\Filament\Resources\FormationTrackResource;
use App\Models\FormationTrack;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateFormationTrack extends CreateRecord
{
    protected static string $resource = FormationTrackResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['approved_by'] = null;

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
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'slug' => 'Ya existe una ruta con este slug en el mismo alcance de iglesia.',
            ]);
        }

        return $data;
    }
}
