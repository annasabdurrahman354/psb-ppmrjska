<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusOrangTua : string implements HasLabel, HasColor {
    case HIDUP = 'hidup';
    case MENINGGAL = 'meninggal';
    case TIDAK_DIKETAHUI = 'tidak diketahui';


    public function getLabel(): ?string
    {
        return match ($this) {
            self::HIDUP => 'Hidup',
            self::MENINGGAL => 'Meninggal',
            self::TIDAK_DIKETAHUI => 'Piatu',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::HIDUP => 'success',
            self::MENINGGAL => 'danger',
            self::TIDAK_DIKETAHUI => 'warning',
        };
    }
}
