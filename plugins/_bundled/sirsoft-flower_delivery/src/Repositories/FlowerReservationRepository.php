<?php

namespace Plugins\Sirsoft\FlowerDelivery\Repositories;

use Illuminate\Support\Collection;
use Plugins\Sirsoft\FlowerDelivery\Models\FlowerReservation;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerReservationRepositoryInterface;

/**
 * 슬롯 예약 Repository 구현체
 */
class FlowerReservationRepository implements FlowerReservationRepositoryInterface
{
    /**
     * 예약을 생성합니다.
     *
     * @param array $data 예약 데이터
     * @return FlowerReservation
     */
    public function create(array $data): FlowerReservation
    {
        return FlowerReservation::create($data);
    }

    /**
     * 사용자의 대기 중 예약을 상품 ID 순으로 조회합니다.
     *
     * @param int $userId 사용자 ID
     * @param array<int> $productIds 상품 ID 목록 (빈 배열이면 전체)
     * @return Collection<int, FlowerReservation>
     */
    public function findPendingByUser(int $userId, array $productIds = []): Collection
    {
        return FlowerReservation::where('user_id', $userId)
            ->where('status', 'pending')
            ->when($productIds !== [], fn ($q) => $q->whereIn('product_id', $productIds))
            ->orderBy('id')
            ->get();
    }

    /**
     * 예약을 소진 처리합니다.
     *
     * @param FlowerReservation $reservation 예약 모델
     * @param int $orderId 소진 주문 ID
     * @return FlowerReservation
     */
    public function markConsumed(FlowerReservation $reservation, int $orderId): FlowerReservation
    {
        $reservation->status = 'consumed';
        $reservation->consumed_order_id = $orderId;
        $reservation->save();

        return $reservation->fresh();
    }
}
