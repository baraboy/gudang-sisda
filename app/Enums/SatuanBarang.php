<?php

namespace App\Enums;

enum SatuanBarang: string
{
    case PCS = 'pcs';
    case SET = 'set';
    case BOX = 'box';
    case UNIT = 'unit';

    public function label(): string
    {
        return match($this) {
            self::PCS => 'PCS',
            self::SET => 'SET',
            self::BOX => 'Box',
            self::UNIT => 'Unit',
        };
    }
}
