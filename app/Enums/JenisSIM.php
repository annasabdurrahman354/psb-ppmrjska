<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JenisSIM : string implements HasLabel, HasColor {
    case A = 'A';
    case B1 = 'B1';
    case B2 = 'B2';
    case C = 'C';
    case D = 'D';
    case A_UMUM = 'A Umum';
    case B1_UMUM = 'B1 Umum';
    case B2_UMUM = 'B2 Umum';
    case INTERNASIONAL = 'Internasional';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::A => 'SIM A',
            self::B1 => 'SIM B1',
            self::B2 => 'SIM B2',
            self::C => 'SIM C',
            self::D => 'SIM D',
            self::A_UMUM => 'SIM A Umum',
            self::B1_UMUM => 'SIM B1 Umum',
            self::B2_UMUM => 'SIM B2 Umum',
            self::INTERNASIONAL => 'SIM Internasional',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::A => 'success',
            self::B1 => 'warning',
            self::B2 => 'danger',
            self::C => 'primary',
            self::D => 'secondary',
            self::A_UMUM => 'info',
            self::B1_UMUM => 'warning',
            self::B2_UMUM => 'danger',
            self::INTERNASIONAL => 'purple',
        };
    }
}
