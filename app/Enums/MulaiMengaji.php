<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MulaiMengaji: string implements HasLabel, HasColor{
    case LAHIR = 'lahir';
    case PAUD = 'paud';
    case TK = 'tk';
    case SD = 'sd';
    case SMP = 'smp';
    case SMA = 'sma';
    case KULIAH = 'kuliah';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::LAHIR => 'Sejak Lahir',
            self::PAUD => 'PAUD',
            self::TK => 'TK',
            self::SD => 'SD',
            self::SMP => 'SMP',
            self::SMA => 'SMA',
            self::KULIAH => 'Kuliah',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::LAHIR => 'primary',
            self::PAUD => 'secondary',
            self::TK => 'info',
            self::SD => 'info',
            self::SMP => 'warning',
            self::SMA => 'warning',
            self::KULIAH => 'danger',
        };
    }
}