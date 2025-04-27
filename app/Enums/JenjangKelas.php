<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JenjangKelas : string implements HasLabel, HasColor {
    case PEGON_BACAAN = 'pegon bacaan';
    case LAMBATAN = 'lambatan';
    case CEPATAN = 'cepatan';
    case SARINGAN = 'saringan';

    case HADIST_BESAR = 'hadist besar';


    public function getLabel(): ?string
    {
        return match ($this) {
            self::PEGON_BACAAN => 'Pegon Bacaan',
            self::LAMBATAN => 'Lambatan',
            self::CEPATAN => 'Cepatan',
            self::SARINGAN => 'Saringan',
            self::HADIST_BESAR => 'Hadist Besar'
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::PEGON_BACAAN => 'warning',
            self::LAMBATAN => 'info',
            self::CEPATAN => 'primary',
            self::SARINGAN => 'success',
            self::HADIST_BESAR => 'secondary',
        };
    }
}
