<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UsersStatus: string implements HasLabel, HasColor{
    case AKTIF = 'aktif';
    case NONAKTIF = 'nonaktif';
    case LULUS = 'lulus';
    case KELUAR = 'keluar';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AKTIF => 'Aktif',
            self::NONAKTIF => 'Nonaktif',
            self::LULUS => 'Lulus',
            self::KELUAR => 'Keluar',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::AKTIF => 'success',
            self::NONAKTIF => 'gray',
            self::LULUS => 'primary',
            self::KELUAR => 'danger',
        };
    }
}
