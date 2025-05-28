<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Role : string implements HasLabel {
    case SUPERADMIN = 'Super Admin';
    case DMC_PASUS_KEILMUAN = 'DMC-Pasus Keilmuan';
    case DMC_PASUS_KEDISIPLINAN = 'DMC-Pasus Kedisiplinan';
    case DMC_PASUS_SEKRETARIS = 'DMC-Pasus Sekretaris';
    case DMC_PASUS_KOORDINATOR = 'DMC-Pasus Koordinator';
    case PANITIA_PSB = 'Panitia PSB';
    case DEWAN_PENGUJI = 'Dewan Penguji';
    case KETUA_KELAS = 'Ketua Kelas';
    case SANTRI = 'Santri';
    case GURU = 'Guru';
    case ALUMNI = 'Alumni';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SUPERADMIN => 'Super Admin',
            self::DMC_PASUS_KEILMUAN => 'DMC-Pasus Keilmuan',
            self::DMC_PASUS_KEDISIPLINAN => 'DMC-Pasus Kedisiplinan',
            self::DMC_PASUS_SEKRETARIS => 'DMC-Pasus Sekretaris',
            self::DMC_PASUS_KOORDINATOR => 'DMC-Pasus Koordinator',
            self::PANITIA_PSB => 'Panitia PSB',
            self::DEWAN_PENGUJI => 'Dewan Penguji',
            self::KETUA_KELAS => 'Ketua Kelas',
            self::SANTRI => 'Santri',
            self::GURU => 'Guru',
            self::ALUMNI => 'Alumni',
        };
    }
}
