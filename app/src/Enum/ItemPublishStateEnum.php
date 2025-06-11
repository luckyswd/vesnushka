<?php

namespace App\Enum;

enum ItemPublishStateEnum: string
{
    /**
     * Отображается в магазине, можно купить.
     */
    case ACTIVE = 'active';

    /**
     * Не отображается в магазине.
     */
    case INACTIVE = 'inactive';

    /**
     * Полностью убран, не отображается в магазине, но не удалён из БД.
     */
    case ARCHIVED = 'archived';

    /**
     * Отображается в магазине, но кнопка "купить" заблокирована или заменена на "Оповестить о поступлении".
     */
    case OUT_OF_STOCK = 'out_of_stock';

    /**
     * Возвращает массив состояний, которые можно показывать на витрине.
     *
     * @return ItemPublishStateEnum[]
     */
    public static function getVisibleStates(): array
    {
        return [
            self::ACTIVE,
            self::OUT_OF_STOCK,
        ];
    }
}
