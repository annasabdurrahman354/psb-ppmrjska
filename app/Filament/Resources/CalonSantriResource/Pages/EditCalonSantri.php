<?php

namespace App\Filament\Resources\CalonSantriResource\Pages;

use App\Filament\Resources\CalonSantriResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCalonSantri extends EditRecord
{
    protected static string $resource = CalonSantriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
