<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusKuliahCalonSantri: string implements HasLabel, HasColor{
    case AKTIF = 'aktif';
    case BELUM_DITERIMA = 'belum diterima';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AKTIF => 'Sudah Diterima',
            self::BELUM_DITERIMA => 'Belum Diterima',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::AKTIF => 'success',
            self::BELUM_DITERIMA => 'danger',
        };
    }
}
