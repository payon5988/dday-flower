<?php

namespace Plugins\Sirsoft\FlowerDelivery\Providers;

use App\Extension\BasePluginServiceProvider;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerDeliverySlotRepositoryInterface;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerFaqRepositoryInterface;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerMessageCardRepositoryInterface;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerOptionRepositoryInterface;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerSubscriptionRepositoryInterface;
use Plugins\Sirsoft\FlowerDelivery\Repositories\FlowerDeliverySlotRepository;
use Plugins\Sirsoft\FlowerDelivery\Repositories\FlowerFaqRepository;
use Plugins\Sirsoft\FlowerDelivery\Repositories\FlowerMessageCardRepository;
use Plugins\Sirsoft\FlowerDelivery\Repositories\FlowerOptionRepository;
use Plugins\Sirsoft\FlowerDelivery\Repositories\FlowerSubscriptionRepository;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerReservationRepositoryInterface;
use Plugins\Sirsoft\FlowerDelivery\Repositories\FlowerReservationRepository;

/**
 * 꽃배달 플러그인 서비스 프로바이더
 *
 * Repository 바인딩은 BasePluginServiceProvider 표준에 위임합니다.
 */
class FlowerDeliveryServiceProvider extends BasePluginServiceProvider
{
    /**
     * 플러그인 식별자
     *
     * @var string
     */
    protected string $pluginIdentifier = 'sirsoft-flower_delivery';

    /**
     * Repository 바인딩 목록
     *
     * @var array<string, string>
     */
    protected array $repositories = [
        FlowerDeliverySlotRepositoryInterface::class => FlowerDeliverySlotRepository::class,
        FlowerFaqRepositoryInterface::class => FlowerFaqRepository::class,
        FlowerMessageCardRepositoryInterface::class => FlowerMessageCardRepository::class,
        FlowerOptionRepositoryInterface::class => FlowerOptionRepository::class,
        FlowerReservationRepositoryInterface::class => FlowerReservationRepository::class,
        FlowerSubscriptionRepositoryInterface::class => FlowerSubscriptionRepository::class,
    ];
}
