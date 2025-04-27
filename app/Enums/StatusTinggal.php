<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusTinggal : string implements HasLabel, HasColor {
    case BERSAMA_ORANG_TUA = 'bersama orang tua';
    case BERSAMA_WALI = 'bersama wali';
    case MANDIRI = 'mandiri';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BERSAMA_ORANG_TUA => 'Bersama Orang Tua',
            self::BERSAMA_WALI => 'Bersama Wali',
            self::MANDIRI => 'Mandiri',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::BERSAMA_ORANG_TUA => 'primary',
            self::BERSAMA_WALI => 'secondary',
            self::MANDIRI => 'info',
        };
    }
}
