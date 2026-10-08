<?php

namespace Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Public;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Plugins\Sirsoft\FlowerDelivery\Services\FlowerFaqService;

/**
 * 꽃배달 FAQ 공개 컨트롤러
 */
class FaqController extends Controller
{
    /**
     * @param FlowerFaqService $faqService FAQ 서비스
     */
    public function __construct(
        private readonly FlowerFaqService $faqService,
    ) {}

    /**
     * 공개 FAQ 목록을 조회합니다 (요청 로케일로 해석).
     *
     * @return JsonResponse FAQ 응답
     */
    public function index(): JsonResponse
    {
        return ResponseHelper::success('sirsoft-flower_delivery::messages.faqs_fetched', [
            'faqs' => $this->faqService->getPublicList((string) app()->getLocale()),
        ]);
    }
}
