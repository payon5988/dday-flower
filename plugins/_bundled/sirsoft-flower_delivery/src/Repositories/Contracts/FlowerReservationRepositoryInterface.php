<?php

namespace Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts;

use Illuminate\Support\Collection;
use Plugins\Sirsoft\FlowerDelivery\Models\FlowerReservation;

/**
 * 슬롯 예약 Repository 인터페이스
 */
interface FlowerReservationRepositoryInterface
{
    /**
     * 예약을 생성합니다.
     *
     * @param array $data 예약 데이터
     * @return FlowerReservation
     */
    public function create(array $data): FlowerReservation;

    /**
     * 사용자의 대기 중 예약을 상품 ID 순으로 조회합니다.
     *
     * @param int $userId 사용자 ID
     * @param array<int> $productIds 상품 ID 목록 (빈 배열이면 전체)
     * @return Collection<int, FlowerReservation>
     */
    public function findPendingByUser(int $userId, array $productIds = []): Collection;

    /**
     * 예약을 소진 처리합니다.
     *
     * @param FlowerReservation $reservation 예약 모델
     * @param int $orderId 소진 주문 ID
     * @return FlowerReservation
     */
    public function markConsumed(FlowerReservation $reservation, int $orderId): FlowerReservation;
}
