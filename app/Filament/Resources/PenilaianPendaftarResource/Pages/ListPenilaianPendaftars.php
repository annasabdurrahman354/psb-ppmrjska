<?php

namespace App\Filament\Resources\PenilaianPendaftarResource\Pages;

use App\Filament\Resources\PenilaianPendaftarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenilaianPendaftars extends ListRecords
{
    protected static string $resource = PenilaianPendaftarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
