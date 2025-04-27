<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BahasaMakna : string implements HasLabel, HasColor {
    case INDONESIA = 'indonesia';
    case JAWA = 'jawa';
    case INGGRIS = 'inggris';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INDONESIA => 'Indonesia',
            self::JAWA => 'Jawa',
            self::INGGRIS => 'Inggris',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::INDONESIA => 'success',
            self::JAWA => 'primary',
            self::INGGRIS => 'info',
        };
    }
}
