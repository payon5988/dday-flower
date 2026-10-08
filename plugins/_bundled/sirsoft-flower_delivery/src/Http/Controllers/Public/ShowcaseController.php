<?php

namespace Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Public;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Plugins\Sirsoft\FlowerDelivery\Http\Requests\IndexDeliverySlotRequest;
use Plugins\Sirsoft\FlowerDelivery\Services\DeliverySlotService;
use Plugins\Sirsoft\FlowerDelivery\Services\FlowerOptionService;
use Plugins\Sirsoft\FlowerDelivery\Services\MessageCardService;
use Plugins\Sirsoft\FlowerDelivery\Services\ShareService;

/**
 * 꽃배달 공개 쇼케이스 컨트롤러
 *
 * 옵션·공유·카드 템플릿·당일 상태 조회 (인증 불필요).
 */
class ShowcaseController extends Controller
{
    /**
     * @param FlowerOptionService $optionService 옵션 서비스
     * @param ShareService $shareService 공유 서비스
     * @param MessageCardService $messageCardService 메시지 카드 서비스
     * @param DeliverySlotService $slotService 슬롯 서비스
     */
    public function __construct(
        private readonly FlowerOptionService $optionService,
        private readonly ShareService $shareService,
        private readonly MessageCardService $messageCardService,
        private readonly DeliverySlotService $slotService,
    ) {}

    /**
     * 상품 맞춤 옵션 목록을 조회합니다.
     *
     * @param int $id 상품 ID
     * @return JsonResponse 옵션 그룹 응답
     */
    public function options(int $id): JsonResponse
    {
        $groups = $this->optionService->getGroupedByProduct($id);
        $flat = [];

        foreach ($groups as $group => $items) {
            foreach ($items as $item) {
                $flat[] = $item + ['group' => $group];
            }
        }

        return ResponseHelper::success(
            'flower_delivery::messages.options_fetched',
            ['groups' => $groups, 'options' => $flat]
        );
    }

    /**
     * 상품 SNS 공유 페이로드를 조회합니다.
     *
     * @param int $id 상품 ID
     * @return JsonResponse 공유 응답
     */
    public function share(int $id): JsonResponse
    {
        $payload = $this->shareService->buildProductShare($id);

        if ($payload === null) {
            return ResponseHelper::error('sirsoft-flower_delivery::messages.product_not_found', 404);
        }

        return ResponseHelper::success('sirsoft-flower_delivery::messages.share_fetched', $payload);
    }

    /**
     * 메시지 카드 템플릿 목록을 조회합니다.
     *
     * @return JsonResponse 템플릿 응답
     */
    public function cardTemplates(): JsonResponse
    {
        return ResponseHelper::success(
            'sirsoft-flower_delivery::messages.card_templates_fetched',
            ['templates' => $this->messageCardService->templates()]
        );
    }

    /**
     * 당일배송 가능 상태를 조회합니다 (마감 자동 계산).
     *
     * @param IndexDeliverySlotRequest $request 검증된 요청
     * @return JsonResponse 당일 상태 응답
     */
    public function dayStatus(IndexDeliverySlotRequest $request): JsonResponse
    {
        $data = $request->validated();
        $date = (string) ($data['date'] ?? now()->toDateString());
        $productId = (int) ($data['product_id'] ?? 1);

        return ResponseHelper::success(
            'sirsoft-flower_delivery::messages.day_status_fetched',
            $this->slotService->getDayStatus($date, $productId)
        );
    }
}
