<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UkuranBaju: string implements HasLabel, HasColor{
    case S = 'S';
    case M = 'M';
    case L = 'L';
    case XL = 'XL';
    case XXL = 'XXL';
    case XXXL = 'XXXL';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::S => 'S',
            self::M => 'M',
            self::L => 'L',
            self::XL => 'XL',
            self::XXL => 'XXL',
            self::XXXL => 'XXXL',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::S => 'primary',
            self::M => 'secondary',
            self::L => 'success',
            self::XL => 'warning',
            self::XXL => 'info',
            self::XXXL => 'danger',
        };
    }
}
