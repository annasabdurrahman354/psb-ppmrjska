<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum GolonganDarah : string implements HasLabel, HasColor {
    case A = 'A';
    case B = 'B';
    case AB = 'AB';
    case O = 'O';
    case TIDAK_TAHU = 'tidak tahu';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::A => 'A',
            self::B => 'B',
            self::AB => 'AB',
            self::O => 'O',
            self::TIDAK_TAHU => 'Tidak Tahu',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::A => 'primary',
            self::B => 'secondary',
            self::AB => 'warning',
            self::O => 'info',
            self::TIDAK_TAHU => 'danger',
        };
    }
}
