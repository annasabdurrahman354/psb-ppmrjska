<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum HubunganWali : string implements HasLabel, HasColor {
    case ORANGTUA = 'orang tua';
    case SAUDARA = 'saudara';
    case KERABAT = 'kerabat';
    case NONKERABAT = 'nonkerabat';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ORANGTUA => 'Orang Tua',
            self::SAUDARA => 'Saudara',
            self::KERABAT => 'Kerabat',
            self::NONKERABAT => 'Nonkerabat',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::ORANGTUA => 'primary',
            self::SAUDARA => 'info',
            self::KERABAT => 'info',
            self::NONKERABAT => 'warning',
        };
    }
}
