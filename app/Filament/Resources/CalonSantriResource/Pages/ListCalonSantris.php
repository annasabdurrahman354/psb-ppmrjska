<?php

namespace App\Filament\Resources\CalonSantriResource\Pages;

use App\Filament\Resources\CalonSantriResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCalonSantris extends ListRecords
{
    protected static string $resource = CalonSantriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
