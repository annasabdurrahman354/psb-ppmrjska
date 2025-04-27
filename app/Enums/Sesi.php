<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Sesi : string implements HasLabel, HasColor {
    case SUBUH = 'subuh';
    case PAGI_1 = 'pagi 1';
    case PAGI_2 = 'pagi 2';
    case SIANG = 'siang';
    case MALAM = 'malam';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SUBUH => 'Subuh',
            self::PAGI_1 => 'Pagi 1',
            self::PAGI_2 => 'Pagi 2',
            self::SIANG => 'Siang',
            self::MALAM => 'Malam',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::SUBUH => 'warning',
            self::PAGI_1 => 'info',
            self::PAGI_2 => 'secondary',
            self::SIANG => 'primary',
            self::MALAM => 'success',
        };
    }

    public function getDefaultKeterlambatan(): ?string
    {
        return match ($this) {
            self::SUBUH => '05:00',
            self::PAGI_1 => '08:45',
            self::PAGI_2 => '10:15',
            self::SIANG => '13:45',
            self::MALAM => '20:00',
        };
    }
}
