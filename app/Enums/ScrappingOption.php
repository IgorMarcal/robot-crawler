<?php

namespace App\Enums;

enum ScrappingOption: string
{
    case OPTION1 = '6';
    case OPTION2 = '8';

    public function getIndices(): array
    {
        return match($this) {
            self::OPTION1 => [
                "Consignações facultativas",
                "Cartao Credito",
                "Cartao De beneficio",
            ],
            self::OPTION2 => [
                "Consignações facultativas",
                "Cartao Credito",
                "Cartao De beneficio",
                "Consignações Compulsórias"
            ]
        };
    }
}
