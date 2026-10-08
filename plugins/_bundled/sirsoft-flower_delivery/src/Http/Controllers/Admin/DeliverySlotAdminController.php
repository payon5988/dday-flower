<?php

namespace Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Plugins\Sirsoft\FlowerDelivery\Http\Requests\BoardDeliverySlotRequest;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerDeliverySlotRepositoryInterface;
use Plugins\Sirsoft\FlowerDelivery\Services\DeliverySlotService;

/**
 * 꽃배달 배송 슬롯 관리자 컨트롤러
 */
class DeliverySlotAdminController extends Controller
{
    /**
     * @param FlowerDeliverySlotRepositoryInterface $slotRepository 슬롯 Repository
     * @param DeliverySlotService $slotService 슬롯 서비스
     */
    public function __construct(
        private readonly FlowerDeliverySlotRepositoryInterface $slotRepository,
        private readonly DeliverySlotService $slotService,
    ) {}

    /**
     * 슬롯 활성/비활성을 전환합니다.
     *
     * @param int $id 슬롯 ID
     * @return JsonResponse 처리 응답
     */
    public function toggle(int $id): JsonResponse
    {
        $slot = $this->slotRepository->findById($id);

        if ($slot === null) {
            return ResponseHelper::error('sirsoft-flower_delivery::messages.slot_not_found', 404);
        }

        $slot->is_active = ! $slot->is_active;
        $slot->save();

        $this->slotService->invalidateSlotCache(
            (int) $slot->product_id,
            $slot->delivery_date instanceof \DateTimeInterface
                ? $slot->delivery_date->format('Y-m-d')
                : (string) $slot->delivery_date,
            $slot->tenant_id !== null ? (int) $slot->tenant_id : null,
        );

        return ResponseHelper::success('sirsoft-flower_delivery::messages.slot_toggled', [
            'id' => $slot->id,
            'is_active' => $slot->is_active,
        ]);
    }

    /**
     * 날짜별 슬롯 현황판(관리자 보드)을 조회합니다.
     *
     * @param \Plugins\Sirsoft\FlowerDelivery\Http\Requests\BoardDeliverySlotRequest $request 검증된 요청
     * @return JsonResponse 현황판 응답
     */
    public function board(BoardDeliverySlotRequest $request): JsonResponse
    {
        $data = $request->validated();
        $date = (string) ($data['date'] ?? now()->toDateString());
        $productId = (int) ($data['product_id'] ?? 1);

        $slots = $this->slotRepository->getByProductAndDate($productId, $date)->map(fn ($slot) => [
            'id' => $slot->id,
            'time' => $slot->time_slot,
            'max_capacity' => $slot->max_capacity,
            'current_bookings' => $slot->current_bookings,
            'remaining' => $slot->remainingCapacity(),
            'is_active' => $slot->is_active,
        ])->all();

        return ResponseHelper::success('sirsoft-flower_delivery::messages.board_fetched', [
            'date' => $date,
            'day' => $this->slotService->getDayStatus($date, $productId),
            'slots' => $slots,
        ]);
    }
}
