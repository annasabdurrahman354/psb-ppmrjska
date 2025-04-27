<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusPernikahan : string implements HasLabel, HasColor {
    case BELUM_MENIKAH = 'belum menikah';
    case MENIKAH = 'menikah';
    case CERAI = 'cerai';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BELUM_MENIKAH => 'Belum Menikah',
            self::MENIKAH => 'Menikah',
            self::CERAI => 'Cerai',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::BELUM_MENIKAH => 'success',
            self::MENIKAH => 'warning',
            self::CERAI => 'danger',
        };
    }
}
