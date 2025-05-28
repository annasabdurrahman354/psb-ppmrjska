<?php

namespace App\Filament\Resources\PenilaianCalonSantriResource\Pages;

use App\Filament\Resources\PenilaianCalonSantriResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPenilaianCalonSantri extends ViewRecord
{
    protected static string $resource = PenilaianCalonSantriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
