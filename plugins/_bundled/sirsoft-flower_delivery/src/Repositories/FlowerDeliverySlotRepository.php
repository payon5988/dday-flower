<?php

namespace Plugins\Sirsoft\FlowerDelivery\Repositories;

use Illuminate\Support\Collection;
use Plugins\Sirsoft\FlowerDelivery\Models\FlowerDeliverySlot;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerDeliverySlotRepositoryInterface;

/**
 * 배송 슬롯 Repository 구현체
 */
class FlowerDeliverySlotRepository implements FlowerDeliverySlotRepositoryInterface
{
    /**
     * 상품·날짜·시간대·테넌트로 슬롯을 조회합니다.
     *
     * @param int $productId 상품 ID
     * @param string $deliveryDate 배송일 (Y-m-d)
     * @param string $timeSlot 시간대
     * @param int|null $tenantId 테넌트 ID
     * @return FlowerDeliverySlot|null
     */
    public function findSlot(int $productId, string $deliveryDate, string $timeSlot, ?int $tenantId = null): ?FlowerDeliverySlot
    {
        return FlowerDeliverySlot::where('product_id', $productId)
            ->whereDate('delivery_date', $deliveryDate)
            ->where('time_slot', $timeSlot)
            ->when($tenantId === null, fn ($q) => $q->whereNull('tenant_id'), fn ($q) => $q->where('tenant_id', $tenantId))
            ->first();
    }

    /**
     * 슬롯을 ID로 조회합니다.
     *
     * @param int $id 슬롯 ID
     * @return FlowerDeliverySlot|null
     */
    public function findById(int $id): ?FlowerDeliverySlot
    {
        return FlowerDeliverySlot::find($id);
    }

    /**
     * 상품·날짜 기준 슬롯 목록을 조회합니다.
     *
     * @param int $productId 상품 ID
     * @param string $deliveryDate 배송일 (Y-m-d)
     * @param int|null $tenantId 테넌트 ID
     * @return Collection<int, FlowerDeliverySlot>
     */
    public function getByProductAndDate(int $productId, string $deliveryDate, ?int $tenantId = null): Collection
    {
        return FlowerDeliverySlot::where('product_id', $productId)
            ->whereDate('delivery_date', $deliveryDate)
            ->when($tenantId === null, fn ($q) => $q->whereNull('tenant_id'), fn ($q) => $q->where('tenant_id', $tenantId))
            ->orderBy('time_slot')
            ->get(['id', 'product_id', 'delivery_date', 'time_slot', 'max_capacity', 'current_bookings', 'is_active']);
    }
}
