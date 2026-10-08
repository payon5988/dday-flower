<?php

namespace Plugins\Sirsoft\FlowerDelivery\Http\Controllers\User;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Plugins\Sirsoft\FlowerDelivery\Http\Requests\StoreSubscriptionRequest;
use Plugins\Sirsoft\FlowerDelivery\Services\SubscriptionService;

/**
 * 꽃배달 정기구독 사용자 컨트롤러
 */
class SubscriptionController extends Controller
{
    /**
     * @param SubscriptionService $subscriptionService 구독 서비스
     */
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /**
     * 정기구독을 신청합니다.
     *
     * @param StoreSubscriptionRequest $request 검증된 요청
     * @return JsonResponse 처리 응답
     */
    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $subscription = $this->subscriptionService->subscribe(
            (int) request()->user()->id,
            (int) $data['product_id'],
            (string) $data['cycle'],
        );

        return ResponseHelper::success('sirsoft-flower_delivery::messages.subscription_created', [
            'id' => $subscription->id,
            'status' => $subscription->status,
            'next_delivery_date' => $subscription->next_delivery_date?->toDateString(),
        ], 201);
    }

    /**
     * 구독 배송을 일시 정지합니다.
     *
     * @param int $id 구독 ID
     * @return JsonResponse 처리 응답
     */
    public function pause(int $id): JsonResponse
    {
        $subscription = $this->subscriptionService->pause($id, (int) request()->user()->id);

        if ($subscription === null) {
            return ResponseHelper::error('sirsoft-flower_delivery::messages.subscription_not_found', 404);
        }

        return ResponseHelper::success('sirsoft-flower_delivery::messages.subscription_paused_done', [
            'id' => $subscription->id,
            'status' => $subscription->status,
        ]);
    }

    /**
     * 일시 정지된 구독 배송을 재개합니다.
     *
     * @param int $id 구독 ID
     * @return JsonResponse 처리 응답
     */
    public function resume(int $id): JsonResponse
    {
        $subscription = $this->subscriptionService->resume($id, (int) request()->user()->id);

        if ($subscription === null) {
            return ResponseHelper::error('sirsoft-flower_delivery::messages.subscription_not_found', 404);
        }

        return ResponseHelper::success('sirsoft-flower_delivery::messages.subscription_resumed_done', [
            'id' => $subscription->id,
            'status' => $subscription->status,
        ]);
    }

    /**
     * 내 구독 목록을 조회합니다.
     *
     * @return JsonResponse 구독 목록 응답
     */
    public function mine(): JsonResponse
    {
        return ResponseHelper::success('sirsoft-flower_delivery::messages.subscriptions_fetched', [
            'subscriptions' => $this->subscriptionService->mine((int) request()->user()->id)->all(),
        ]);
    }
}
