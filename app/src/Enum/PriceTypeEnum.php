<?php

namespace App\Enum;

enum PriceTypeEnum: string
{
    case PURCHASE = 'purchase';            // Закупочная цена у поставщика
    case FULL_PURCHASE = 'full_purchase';  // Закупочная с налогами (полная себестоимость)
    case WHOLESALE = 'wholesale';          // Оптовая цена
    case RETAIL = 'retail';                // Розничная цена (финальная цена для покупателя)
}
