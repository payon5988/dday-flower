<?php

namespace Plugins\Sirsoft\FlowerDelivery\Services;

use App\Extension\HookManager;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerOptionRepositoryInterface;

/**
 * 상품 맞춤 옵션 서비스
 */
class FlowerOptionService
{
    /**
     * @param FlowerOptionRepositoryInterface $optionRepository 옵션 Repository
     */
    public function __construct(
        private readonly FlowerOptionRepositoryInterface $optionRepository,
    ) {}

    /**
     * 상품의 활성 옵션을 그룹별로 묶어 반환합니다.
     *
     * @param int $productId 상품 ID
     * @param int|null $tenantId 테넌트 ID
     * @return array<string, array<int, array{key: string, label: string, price_delta: int, color_code: string|null}>>
     */
    public function getGroupedByProduct(int $productId, ?int $tenantId = null): array
    {
        $grouped = [];

        foreach ($this->optionRepository->getActiveByProduct($productId, $tenantId) as $option) {
            $grouped[$option->option_group][] = [
                'key' => $option->option_key,
                'label' => $option->label,
                'price_delta' => $option->price_delta,
                'color_code' => $option->color_code,
            ];
        }

        HookManager::doAction('sirsoft-flower_delivery.options.listed', $productId, $grouped);

        return $grouped;
    }
}
