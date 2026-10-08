<?php

namespace Plugins\Sirsoft\FlowerDelivery\Repositories;

use Illuminate\Support\Collection;
use Plugins\Sirsoft\FlowerDelivery\Models\FlowerFaq;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerFaqRepositoryInterface;

/**
 * FAQ Repository 구현체
 */
class FlowerFaqRepository implements FlowerFaqRepositoryInterface
{
    /**
     * 활성 FAQ 목록을 조회합니다.
     *
     * @param int|null $tenantId 테넌트 ID
     * @return Collection<int, FlowerFaq>
     */
    public function getActive(?int $tenantId = null): Collection
    {
        return FlowerFaq::where('is_active', true)
            ->when($tenantId === null, fn ($q) => $q->whereNull('tenant_id'), fn ($q) => $q->where('tenant_id', $tenantId))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'category', 'question', 'answer', 'sort_order']);
    }

    /**
     * 전체 FAQ 목록을 조회합니다 (관리자용).
     *
     * @return Collection<int, FlowerFaq>
     */
    public function getAll(): Collection
    {
        return FlowerFaq::orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * FAQ를 단건 조회합니다.
     *
     * @param int $id FAQ ID
     * @return FlowerFaq|null
     */
    public function findById(int $id): ?FlowerFaq
    {
        return FlowerFaq::find($id);
    }

    /**
     * FAQ를 생성합니다.
     *
     * @param array $data FAQ 데이터
     * @return FlowerFaq
     */
    public function create(array $data): FlowerFaq
    {
        return FlowerFaq::create($data);
    }

    /**
     * FAQ를 수정합니다.
     *
     * @param FlowerFaq $faq FAQ 모델
     * @param array $data 수정 데이터
     * @return FlowerFaq
     */
    public function update(FlowerFaq $faq, array $data): FlowerFaq
    {
        $faq->fill($data)->save();

        return $faq->fresh();
    }

    /**
     * FAQ를 삭제합니다.
     *
     * @param FlowerFaq $faq FAQ 모델
     * @return void
     */
    public function delete(FlowerFaq $faq): void
    {
        $faq->delete();
    }
}
