<?php

namespace App\Enum;

enum PaymentStatusEnum: string
{
    case NEW = 'new';          // только создано
    case PENDING = 'pending';  // ожидает подтверждения (если банк в hold)
    case PAID = 'paid';        // оплачено
    case FAILED = 'failed';    // ошибка платежа
    case REFUNDED = 'refunded'; // возвращено
    case CANCELED = 'canceled'; // отменено клиентом или магазином
}
