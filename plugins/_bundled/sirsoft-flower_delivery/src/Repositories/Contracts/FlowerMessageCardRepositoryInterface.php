<?php

namespace Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts;

use Plugins\Sirsoft\FlowerDelivery\Models\FlowerMessageCard;

/**
 * 메시지 카드 Repository 인터페이스
 */
interface FlowerMessageCardRepositoryInterface
{
    /**
     * 주문 ID로 메시지 카드를 조회합니다.
     *
     * @param int $orderId 주문 ID
     * @return FlowerMessageCard|null
     */
    public function findByOrderId(int $orderId): ?FlowerMessageCard;

    /**
     * 주문 ID 목록으로 메시지 카드를 조회합니다.
     *
     * @param array<int> $orderIds 주문 ID 목록
     * @return \Illuminate\Support\Collection<int, FlowerMessageCard>
     */
    public function findByOrderIds(array $orderIds): \Illuminate\Support\Collection;

    /**
     * 메시지 카드를 저장합니다.
     *
     * @param array $data 저장 데이터
     * @return FlowerMessageCard
     */
    public function store(array $data): FlowerMessageCard;
}
