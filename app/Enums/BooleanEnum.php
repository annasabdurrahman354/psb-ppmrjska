<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum BooleanEnum: int implements HasIcon, HasColor{
    case false = 0;
    case true = 1;

    public function getColor(): ?string
    {
        return match ($this) {
            self::false => 'danger',
            self::true => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::false => 'heroicon-s-x-circle',
            self::true => 'heroicon-s-check-circle',
        };
    }
}