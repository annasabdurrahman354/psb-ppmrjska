<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PendidikanTerakhir: string implements HasLabel, HasColor{
    case SMA = 'SMA';
    case SMK = 'SMK';
    case PAKET_C = 'Paket C';
    case S1 = 'S1';
    case S2 = 'S2';
    case D1 = 'D1';
    case D2 = 'D2';
    case D3 = 'D3';
    case D4 = 'D4';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SMA => 'SMA',
            self::SMK => 'SMK',
            self::PAKET_C => 'Paket C',
            self::S1 => 'S1',
            self::S2 => 'S2',
            self::D1 => 'D1',
            self::D2 => 'D2',
            self::D3 => 'D3',
            self::D4 => 'D4',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::SMA => 'primary',
            self::SMK => 'primary',
            self::PAKET_C => 'secondary',
            self::S1 => 'info',
            self::S2 => 'info',
            self::D1 => 'warning',
            self::D2 => 'warning',
            self::D3 => 'warning',
            self::D4 => 'warning',
        };
    }
}
