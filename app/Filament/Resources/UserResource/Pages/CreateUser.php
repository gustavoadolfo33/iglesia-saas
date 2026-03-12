<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return UserResource::normalizeManagementData($data);
    }

    protected function afterCreate(): void
    {
        $normalized = UserResource::normalizeManagementData($this->data);

        $this->record->syncRoles($normalized['roles'] ?? []);

        if (UserResource::isPastorManager()) {
            $this->record->syncPermissions($normalized['extra_permissions'] ?? []);
        }
    }
}
