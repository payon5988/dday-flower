<?php

namespace Plugins\Sirsoft\FlowerDelivery\Http\Controllers\User;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Plugins\Sirsoft\FlowerDelivery\Services\MessageCardService;

/**
 * 꽃배달 메시지 카드 사용자 컨트롤러
 */
class MessageCardController extends Controller
{
    /**
     * @param MessageCardService $messageCardService 메시지 카드 서비스
     */
    public function __construct(
        private readonly MessageCardService $messageCardService,
    ) {}

    /**
     * 내 메시지 카드 아카이브를 조회합니다 (본인 주문 기준).
     *
     * @return JsonResponse 아카이브 응답
     */
    public function mine(): JsonResponse
    {
        return ResponseHelper::success('sirsoft-flower_delivery::messages.cards_fetched', [
            'cards' => $this->messageCardService->archiveByUser((int) request()->user()->id)->all(),
        ]);
    }
}
