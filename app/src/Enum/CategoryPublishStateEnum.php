<?php

namespace App\Enum;

enum CategoryPublishStateEnum: string
{
    /**
     * Категория отображается в магазине, доступна для пользователей.
     */
    case ACTIVE = 'active';

    /**
     * Категория скрыта в магазине (например, временно отключена).
     */
    case INACTIVE = 'inactive';

    /**
     * Категория полностью убрана из магазина, но не удалена из БД (для истории или SEO).
     */
    case ARCHIVED = 'archived';
}
