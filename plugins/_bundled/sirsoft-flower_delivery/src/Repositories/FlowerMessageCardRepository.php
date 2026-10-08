<?php

namespace Plugins\Sirsoft\FlowerDelivery\Repositories;

use Plugins\Sirsoft\FlowerDelivery\Models\FlowerMessageCard;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerMessageCardRepositoryInterface;

/**
 * 메시지 카드 Repository 구현체
 */
class FlowerMessageCardRepository implements FlowerMessageCardRepositoryInterface
{
    /**
     * 주문 ID로 메시지 카드를 조회합니다.
     *
     * @param int $orderId 주문 ID
     * @return FlowerMessageCard|null
     */
    public function findByOrderId(int $orderId): ?FlowerMessageCard
    {
        return FlowerMessageCard::where('order_id', $orderId)->first();
    }

    /**
     * 주문 ID 목록으로 메시지 카드를 조회합니다.
     *
     * @param array<int> $orderIds 주문 ID 목록
     * @return \Illuminate\Support\Collection<int, FlowerMessageCard>
     */
    public function findByOrderIds(array $orderIds): \Illuminate\Support\Collection
    {
        return FlowerMessageCard::whereIn('order_id', $orderIds)
            ->orderByDesc('id')
            ->get(['id', 'order_id', 'sender_name', 'recipient_name', 'encrypted_message', 'created_at']);
    }

    /**
     * 메시지 카드를 저장합니다.
     *
     * @param array $data 저장 데이터
     * @return FlowerMessageCard
     */
    public function store(array $data): FlowerMessageCard
    {
        return FlowerMessageCard::create($data);
    }
}
