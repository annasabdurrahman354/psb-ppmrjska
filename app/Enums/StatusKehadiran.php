<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusKehadiran : string implements HasLabel, HasColor {
    case HADIR = 'hadir';
    case TELAT = 'telat';
    case IZIN = 'izin';
    case SAKIT = 'sakit';
    case ALPA = 'alpa';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::HADIR => 'Hadir',
            self::TELAT => 'Telat',
            self::IZIN => 'Izin',
            self::SAKIT => 'Sakit',
            self::ALPA => 'Alpa',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::HADIR => 'success',
            self::TELAT => 'primary',
            self::IZIN => 'warning',
            self::SAKIT => 'secondary',
            self::ALPA => 'danger',
        };
    }
}
