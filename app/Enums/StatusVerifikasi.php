<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusVerifikasi : string implements HasLabel, HasColor {
    case BELUM_TERVERIFIKASI = 'belum terverifikasi';
    case TERVERIFIKASI = 'terverifikasi';
    case DITOLAK = 'ditolak';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BELUM_TERVERIFIKASI => 'Belum Terverifikasi',
            self::TERVERIFIKASI => 'Terverifikasi',
            self::DITOLAK => 'Ditolak',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::BELUM_TERVERIFIKASI => 'info',
            self::TERVERIFIKASI => 'success',
            self::DITOLAK => 'danger',
        };
    }
}
