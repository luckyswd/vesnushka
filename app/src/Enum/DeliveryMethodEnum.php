<?php

namespace App\Enum;

use Symfony\Component\Serializer\Attribute\Groups;

#[Groups(['json_cart'])]
enum DeliveryMethodEnum: string
{
    case EUROPOST_PICKUP = 'europost_pickup';            // Европочта (самовывоз из отделения)
    case EUROPOST_COURIER = 'europost_courier';          // Европочта (курьером до двери)
    case BELPOST_PICKUP = 'belpost_pickup';              // Белпочта (самовывоз из отделения)
    case BELPOST_COURIER = 'belpost_courier';            // Белпочта (курьером до двери)

    /**
     * Возвращает текстовое название метода доставки для отображения пользователю.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::EUROPOST_PICKUP => 'Европочта (самовывоз из отделения)',
            self::EUROPOST_COURIER => 'Европочта (курьером до двери)',
            self::BELPOST_PICKUP => 'Белпочта (самовывоз из отделения)',
            self::BELPOST_COURIER => 'Белпочта (курьером до двери)',
        };
    }

    /**
     * Возвращает стоимость доставки в рублях.
     */
    public function getPrice(): float
    {
        return match ($this) {
            self::EUROPOST_PICKUP => 5.00,
            self::EUROPOST_COURIER => 13.00,
            self::BELPOST_PICKUP => 7.00,
            self::BELPOST_COURIER => 13.00,
        };
    }

    /**
     * Возвращает минимальную сумму заказа для бесплатной доставки по данному методу.
     */
    public function getFreeDeliveryThreshold(): float
    {
        return match ($this) {
            self::EUROPOST_PICKUP => 60.00,
            self::EUROPOST_COURIER => 200.00,
            self::BELPOST_PICKUP => 80.00,
            self::BELPOST_COURIER => 200.00,
        };
    }

    /**
     * Возвращает срок доставки для данного метода.
     */
    public function getDeliveryTime(): string
    {
        return match ($this) {
            self::EUROPOST_PICKUP => '2-3 рабочих дня',
            self::EUROPOST_COURIER => '2-3 рабочих дня',
            self::BELPOST_PICKUP => '2-4 рабочих дня',
            self::BELPOST_COURIER => '2-3 рабочих дня',
        };
    }

    /**
     * Возвращает процент комиссии для данного метода доставки,
     * если она предусмотрена (для Европочты).
     */
    public function getCommission(): ?float
    {
        return match ($this) {
            self::EUROPOST_PICKUP => 1.5,
            self::EUROPOST_COURIER => 1.5,
            self::BELPOST_PICKUP => null,
            self::BELPOST_COURIER => null,
        };
    }
}
