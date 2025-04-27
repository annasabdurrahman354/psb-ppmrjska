<?php

namespace App\Filament\Resources\PenilaianPendaftarResource\Pages;

use App\Filament\Resources\PenilaianPendaftarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenilaianPendaftar extends EditRecord
{
    protected static string $resource = PenilaianPendaftarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
