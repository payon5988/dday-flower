<?php

namespace Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Public;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Plugins\Sirsoft\FlowerDelivery\Exceptions\DeliverySlotFullException;
use Plugins\Sirsoft\FlowerDelivery\Http\Requests\IndexDeliverySlotRequest;
use Plugins\Sirsoft\FlowerDelivery\Http\Requests\ReserveDeliverySlotRequest;
use Plugins\Sirsoft\FlowerDelivery\Http\Resources\DeliverySlotResource;
use Plugins\Sirsoft\FlowerDelivery\Services\DeliverySlotService;

/**
 * 꽃배달 배송 슬롯 공개 컨트롤러
 */
class DeliverySlotController extends Controller
{
    /**
     * @param DeliverySlotService $slotService 슬롯 서비스
     */
    public function __construct(
        private readonly DeliverySlotService $slotService,
    ) {}

    /**
     * 배송 슬롯 목록을 조회합니다.
     *
     * @param IndexDeliverySlotRequest $request 검증된 요청
     * @param int $id 상품 ID
     * @return JsonResponse 슬롯 목록 응답
     */
    public function index(IndexDeliverySlotRequest $request, int $id): JsonResponse
    {
        $date = (string) ($request->validated()['date'] ?? now()->addDay()->toDateString());

        return $this->slotListResponse($id, $date);
    }

    /**
     * 상품 코드로 배송 슬롯 목록을 조회합니다 (상세 페이지용).
     *
     * @param IndexDeliverySlotRequest $request 검증된 요청
     * @param string $code 상품 코드
     * @return JsonResponse 슬롯 목록 응답
     */
    public function indexByCode(IndexDeliverySlotRequest $request, string $code): JsonResponse
    {
        $product = \Modules\Sirsoft\Ecommerce\Models\Product::where('product_code', $code)->first();

        if ($product === null) {
            return ResponseHelper::error('sirsoft-flower_delivery::messages.product_not_found', 404);
        }

        $date = (string) ($request->validated()['date'] ?? now()->addDay()->toDateString());

        return $this->slotListResponse((int) $product->id, $date);
    }

    /**
     * 슬롯 목록 응답을 생성합니다.
     *
     * @param int $productId 상품 ID
     * @param string $date 배송일 (Y-m-d)
     * @return JsonResponse 슬롯 목록 응답
     */
    private function slotListResponse(int $productId, string $date): JsonResponse
    {
        $slots = $this->slotService->getAvailableSlots($productId, $date);

        return ResponseHelper::success(
            'sirsoft-flower_delivery::messages.slots_fetched',
            ['date' => $date, 'slots' => DeliverySlotResource::collection($slots)->resolve()]
        );
    }

    /**
     * 배송 슬롯을 예약합니다 (동시성 안전).
     *
     * @param ReserveDeliverySlotRequest $request 검증된 요청
     * @return JsonResponse 예약 응답
     */
    public function reserve(ReserveDeliverySlotRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $result = $this->slotService->bookSlot(
                (int) $data['product_id'],
                (string) $data['delivery_date'],
                (string) $data['time_slot'],
                isset($data['tenant_id']) ? (int) $data['tenant_id'] : null,
                $request->user() ? (int) $request->user()->id : null,
                $data['message_card'] ?? null,
                $data['selected_options'] ?? null,
            );
        } catch (DeliverySlotFullException $e) {
            return ResponseHelper::error($e->getMessageKey(), 422, null, $e->getMessageParams());
        }

        /** @var \Plugins\Sirsoft\FlowerDelivery\Models\FlowerDeliverySlot $slot */
        $slot = $result['slot'];
        $reservation = $result['reservation'];

        return ResponseHelper::success('sirsoft-flower_delivery::messages.slot_reserved', [
            'reservation_id' => $reservation?->reservation_code ?? sprintf('RSV-%s-%04d', now()->format('Ymd'), $slot->id),
            'expires_in' => 900,
        ]);
    }
}
