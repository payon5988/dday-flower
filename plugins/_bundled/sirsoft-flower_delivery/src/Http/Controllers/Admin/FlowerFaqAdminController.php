<?php

namespace Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Plugins\Sirsoft\FlowerDelivery\Http\Requests\StoreFlowerFaqRequest;
use Plugins\Sirsoft\FlowerDelivery\Services\FlowerFaqService;

/**
 * 꽃배달 FAQ 관리자 컨트롤러
 */
class FlowerFaqAdminController extends Controller
{
    /**
     * @param FlowerFaqService $faqService FAQ 서비스
     */
    public function __construct(
        private readonly FlowerFaqService $faqService,
    ) {}

    /**
     * FAQ 전체 목록을 조회합니다.
     *
     * @return JsonResponse 목록 응답
     */
    public function index(): JsonResponse
    {
        return ResponseHelper::success('sirsoft-flower_delivery::messages.faqs_fetched', [
            'faqs' => $this->faqService->getAdminList()->toArray(),
        ]);
    }

    /**
     * FAQ를 생성합니다.
     *
     * @param StoreFlowerFaqRequest $request 검증된 요청
     * @return JsonResponse 생성 응답
     */
    public function store(StoreFlowerFaqRequest $request): JsonResponse
    {
        $faq = $this->faqService->create($request->validated());

        return ResponseHelper::success('sirsoft-flower_delivery::messages.faq_created', [
            'id' => $faq->id,
        ], 201);
    }

    /**
     * FAQ를 수정합니다.
     *
     * @param StoreFlowerFaqRequest $request 검증된 요청
     * @param int $id FAQ ID
     * @return JsonResponse 수정 응답
     */
    public function update(StoreFlowerFaqRequest $request, int $id): JsonResponse
    {
        $faq = $this->faqService->update($id, $request->validated());

        if ($faq === null) {
            return ResponseHelper::error('sirsoft-flower_delivery::messages.faq_not_found', 404);
        }

        return ResponseHelper::success('sirsoft-flower_delivery::messages.faq_updated', [
            'id' => $faq->id,
        ]);
    }

    /**
     * FAQ를 삭제합니다.
     *
     * @param int $id FAQ ID
     * @return JsonResponse 삭제 응답
     */
    public function destroy(int $id): JsonResponse
    {
        if (! $this->faqService->delete($id)) {
            return ResponseHelper::error('sirsoft-flower_delivery::messages.faq_not_found', 404);
        }

        return ResponseHelper::success('sirsoft-flower_delivery::messages.faq_deleted', [
            'id' => $id,
        ]);
    }

    /**
     * FAQ 활성 상태를 전환합니다.
     *
     * @param int $id FAQ ID
     * @return JsonResponse 전환 응답
     */
    public function toggle(int $id): JsonResponse
    {
        $faq = $this->faqService->toggle($id);

        if ($faq === null) {
            return ResponseHelper::error('sirsoft-flower_delivery::messages.faq_not_found', 404);
        }

        return ResponseHelper::success('sirsoft-flower_delivery::messages.faq_toggled', [
            'id' => $faq->id,
            'is_active' => $faq->is_active,
        ]);
    }
}
