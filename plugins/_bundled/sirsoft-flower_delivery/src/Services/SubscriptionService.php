<?php

namespace Plugins\Sirsoft\FlowerDelivery\Services;

use Plugins\Sirsoft\FlowerDelivery\Enums\SubscriptionCycle;
use Plugins\Sirsoft\FlowerDelivery\Enums\SubscriptionStatus;
use Plugins\Sirsoft\FlowerDelivery\Models\FlowerSubscription;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerSubscriptionRepositoryInterface;

/**
 * 정기구독 서비스
 */
class SubscriptionService
{
    /**
     * @param FlowerSubscriptionRepositoryInterface $subscriptionRepository 구독 Repository
     */
    public function __construct(
        private readonly FlowerSubscriptionRepositoryInterface $subscriptionRepository,
    ) {}

    /**
     * 정기구독을 신청합니다.
     *
     * @param int $userId 구독자 ID
     * @param int $productId 정기구독 상품 ID
     * @param string $cycle 구독 주기 (weekly/monthly)
     * @return FlowerSubscription 생성된 구독
     */
    public function subscribe(int $userId, int $productId, string $cycle): FlowerSubscription
    {
        $next = $cycle === SubscriptionCycle::Monthly->value
            ? now()->addMonth()->toDateString()
            : now()->addWeek()->toDateString();

        return $this->subscriptionRepository->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'cycle' => $cycle,
            'next_delivery_date' => $next,
            'status' => SubscriptionStatus::Active->value,
        ]);
    }

    /**
     * 구독 배송을 일시 정지합니다.
     *
     * @param int $id 구독 ID
     * @param int $userId 요청자 ID (소유권 검증용)
     * @return FlowerSubscription|null 변경된 구독 (없거나 소유권 불일치 시 null)
     */
    public function pause(int $id, int $userId): ?FlowerSubscription
    {
        $subscription = $this->subscriptionRepository->findById($id);

        if ($subscription === null || (int) $subscription->user_id !== $userId) {
            return null;
        }

        return $this->subscriptionRepository->updateStatus($subscription, SubscriptionStatus::Paused->value);
    }

    /**
     * 일시 정지된 구독 배송을 재개합니다.
     *
     * @param int $id 구독 ID
     * @param int $userId 요청자 ID (소유권 검증용)
     * @return FlowerSubscription|null 변경된 구독 (없거나 소유권 불일치 시 null)
     */
    public function resume(int $id, int $userId): ?FlowerSubscription
    {
        $subscription = $this->subscriptionRepository->findById($id);

        if ($subscription === null || (int) $subscription->user_id !== $userId) {
            return null;
        }

        return $this->subscriptionRepository->updateStatus($subscription, SubscriptionStatus::Active->value);
    }

    /**
     * 사용자의 구독 목록을 조회합니다.
     *
     * @param int $userId 사용자 ID
     * @return \Illuminate\Support\Collection<int, FlowerSubscription>
     */
    public function mine(int $userId): \Illuminate\Support\Collection
    {
        return $this->subscriptionRepository->getByUserId($userId);
    }
}
