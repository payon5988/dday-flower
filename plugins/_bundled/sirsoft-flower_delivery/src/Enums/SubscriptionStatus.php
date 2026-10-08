<?php

namespace Plugins\Sirsoft\FlowerDelivery\Enums;

/**
 * 구독 상태 Enum
 */
enum SubscriptionStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Cancelled = 'cancelled';

    /**
     * 라벨을 반환합니다.
     *
     * @return string 라벨
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => __('sirsoft-flower_delivery::messages.subscription_active'),
            self::Paused => __('sirsoft-flower_delivery::messages.subscription_paused'),
            self::Cancelled => __('sirsoft-flower_delivery::messages.subscription_cancelled'),
        };
    }
}
