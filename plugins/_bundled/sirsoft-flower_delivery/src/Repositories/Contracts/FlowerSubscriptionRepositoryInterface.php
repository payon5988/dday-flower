<?php

namespace Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts;

use Plugins\Sirsoft\FlowerDelivery\Models\FlowerSubscription;

/**
 * 정기구독 Repository 인터페이스
 */
interface FlowerSubscriptionRepositoryInterface
{
    /**
     * 구독을 생성합니다.
     *
     * @param array $data 구독 데이터
     * @return FlowerSubscription
     */
    public function create(array $data): FlowerSubscription;

    /**
     * 구독을 단건 조회합니다.
     *
     * @param int $id 구독 ID
     * @return FlowerSubscription|null
     */
    public function findById(int $id): ?FlowerSubscription;

    /**
     * 사용자의 구독 목록을 조회합니다.
     *
     * @param int $userId 사용자 ID
     * @return \Illuminate\Support\Collection<int, FlowerSubscription>
     */
    public function getByUserId(int $userId): \Illuminate\Support\Collection;

    /**
     * 구독 상태를 변경합니다.
     *
     * @param FlowerSubscription $subscription 구독 모델
     * @param string $status 변경 상태
     * @return FlowerSubscription
     */
    public function updateStatus(FlowerSubscription $subscription, string $status): FlowerSubscription;
}
