<?php

namespace App\Filament\Resources\MovementCategoryResource\Pages;

use App\Filament\Resources\MovementCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMovementCategories extends ListRecords
{
    protected static string $resource = MovementCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
