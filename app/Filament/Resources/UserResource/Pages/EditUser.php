<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['roles'] = $this->record->roles->pluck('name')->all();
        $data['base_role'] = collect($data['roles'])
            ->first(fn(string $role) => in_array($role, \App\Models\User::PASTOR_ASSIGNABLE_LOCAL_ROLES, true));
        $data['churches'] = $this->record->churches->modelKeys();
        $data['extra_permissions'] = UserResource::getDirectAdditionalPermissionsForRecord($this->record);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return UserResource::normalizeManagementData($data);
    }

    protected function afterSave(): void
    {
        $normalized = UserResource::normalizeManagementData($this->data);

        $this->record->syncRoles($normalized['roles'] ?? []);

        if (UserResource::isPastorManager()) {
            $this->record->syncPermissions($normalized['extra_permissions'] ?? []);
        }
    }
}
