<?php

namespace Plugins\Sirsoft\FlowerDelivery\Services;

use Illuminate\Support\Collection;
use Plugins\Sirsoft\FlowerDelivery\Models\FlowerFaq;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerFaqRepositoryInterface;

/**
 * FAQ 서비스
 */
class FlowerFaqService
{
    /**
     * @param FlowerFaqRepositoryInterface $faqRepository FAQ Repository
     */
    public function __construct(
        private readonly FlowerFaqRepositoryInterface $faqRepository,
    ) {}

    /**
     * 공개 FAQ 목록을 로케일로 해석해 반환합니다.
     *
     * @param string $locale 로케일
     * @param int|null $tenantId 테넌트 ID
     * @return array<int, array{id: int, category: string, question: string, answer: string}>
     */
    public function getPublicList(string $locale, ?int $tenantId = null): array
    {
        return $this->faqRepository->getActive($tenantId)
            ->map(fn (FlowerFaq $faq) => [
                'id' => $faq->id,
                'category' => $faq->category,
                'question' => FlowerFaq::localize($faq->question, $locale),
                'answer' => FlowerFaq::localize($faq->answer, $locale),
            ])
            ->all();
    }

    /**
     * 관리자 FAQ 목록을 반환합니다.
     *
     * @return Collection<int, FlowerFaq>
     */
    public function getAdminList(): Collection
    {
        return $this->faqRepository->getAll();
    }

    /**
     * FAQ를 생성합니다.
     *
     * @param array $data FAQ 데이터
     * @return FlowerFaq
     */
    public function create(array $data): FlowerFaq
    {
        return $this->faqRepository->create($data);
    }

    /**
     * FAQ를 수정합니다 (없으면 null).
     *
     * @param int $id FAQ ID
     * @param array $data 수정 데이터
     * @return FlowerFaq|null
     */
    public function update(int $id, array $data): ?FlowerFaq
    {
        $faq = $this->faqRepository->findById($id);

        if ($faq === null) {
            return null;
        }

        return $this->faqRepository->update($faq, $data);
    }

    /**
     * FAQ를 삭제합니다 (없으면 false).
     *
     * @param int $id FAQ ID
     * @return bool 삭제 여부
     */
    public function delete(int $id): bool
    {
        $faq = $this->faqRepository->findById($id);

        if ($faq === null) {
            return false;
        }

        $this->faqRepository->delete($faq);

        return true;
    }

    /**
     * FAQ 활성 상태를 전환합니다 (없으면 null).
     *
     * @param int $id FAQ ID
     * @return FlowerFaq|null
     */
    public function toggle(int $id): ?FlowerFaq
    {
        $faq = $this->faqRepository->findById($id);

        if ($faq === null) {
            return null;
        }

        return $this->faqRepository->update($faq, ['is_active' => ! $faq->is_active]);
    }
}
