<?php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole : string implements HasLabel {
    case SANTRI = 'santri';
    case GURU = 'guru';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SANTRI => 'Santri',
            self::GURU => 'guru',
        };
    }
}
