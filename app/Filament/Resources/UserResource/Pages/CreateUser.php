<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return UserResource::normalizeManagementData($data);
    }

    protected function afterCreate(): void
    {
        $normalized = UserResource::normalizeManagementData($this->data);

        UserResource::syncChurchAssignments($this->record, $normalized);

        $this->record->syncRoles($normalized['roles'] ?? []);

        if (UserResource::isPastorManager()) {
            $this->record->syncPermissions($normalized['extra_permissions'] ?? []);
            return;
        }

        if (($normalized['user_scope'] ?? null) === 'global') {
            $this->record->syncPermissions($normalized['global_permissions'] ?? []);
        }
    }
}
