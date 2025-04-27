<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use phpDocumentor\Reflection\Types\Boolean;

enum StatusMubaligh : int implements HasLabel, HasColor, HasIcon {
    case SUDAH = 1;
    case BELUM = 0;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SUDAH => 'Sudah',
            self::BELUM => 'Belum',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::SUDAH => 'info',
            self::BELUM => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BELUM => 'heroicon-s-x-circle',
            self::SUDAH => 'heroicon-s-check-circle',
        };
    }
}
