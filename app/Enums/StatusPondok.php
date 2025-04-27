<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusPondok : string implements HasLabel, HasColor {
    case AKTIF = 'aktif';
    case SAMBANG = 'sambang';
    case KEPERLUAN_AKADEMIK = 'keperluan akademik';
    case NONAKTIF = 'nonaktif';
    case LULUS = 'lulus';
    case KELUAR = 'keluar';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AKTIF => 'Aktif',
            self::SAMBANG => 'Sambang',
            self::KEPERLUAN_AKADEMIK => 'Keperluan Akademik',
            self::NONAKTIF => 'Nonaktif',
            self::LULUS => 'Lulus',
            self::KELUAR => 'Keluar',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::AKTIF => 'success',
            self::SAMBANG => 'info',
            self::KEPERLUAN_AKADEMIK => 'info',
            self::NONAKTIF => 'gray',
            self::LULUS => 'primary',
            self::KELUAR => 'danger',
        };
    }
}
