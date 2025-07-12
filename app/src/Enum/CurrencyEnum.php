<?php

namespace App\Enum;

use Symfony\Component\Serializer\Attribute\Groups;

#[Groups(['json_cart'])]
enum CurrencyEnum: string
{
    case BYN = 'BYN';
    case USD = 'USD';
    case RUB = 'RUB';
}
