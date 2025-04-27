<?php

namespace App\Filament\Resources\PenilaianPendaftarResource\Pages;

use App\Filament\Resources\PenilaianPendaftarResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPenilaianPendaftar extends ViewRecord
{
    protected static string $resource = PenilaianPendaftarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
