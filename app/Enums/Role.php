<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Role : string implements HasLabel {
    case SUPERADMIN = 'Super Admin';
    case ADMIN = 'Admin';
    case GURU = 'Guru';
    case DMC_PASUS_KEILMUAN = 'DMC-Pasus Keilmuan';
    case TIM_KEILMUAN = 'Tim Keilmuan';
    case DMC_PASUS_KEDISIPLINAN = 'DMC-Pasus Kedisiplinan';
    case DMC_PASUS_SEKRETARIS = 'DMC-Pasus Sekretaris';
    case DMC_PASUS_KOORDINATOR = 'DMC-Pasus Koordinator';
    case KETUA_KELAS = 'Ketua Kelas';
    case SANTRI = 'Santri';
    case DEWAN_PENGUJI = 'Dewan Penguji';
    case ALUMNI = 'Alumni';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SUPERADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::GURU => 'Guru',
            self::DMC_PASUS_KEILMUAN => 'DMC-Pasus Keilmuan',
            self::TIM_KEILMUAN => 'Tim Keilmuan',
            self::DMC_PASUS_KEDISIPLINAN => 'DMC-Pasus Kedisiplinan',
            self::DMC_PASUS_SEKRETARIS => 'DMC-Pasus Sekretaris',
            self::DMC_PASUS_KOORDINATOR => 'DMC-Pasus Koordinator',
            self::KETUA_KELAS => 'Ketua Kelas',
            self::SANTRI => 'Santri',
            self::DEWAN_PENGUJI => 'Dewan Penguji',
            self::ALUMNI => 'Alumni',
        };
    }
}
