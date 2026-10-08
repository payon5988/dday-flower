<?php

namespace Plugins\Sirsoft\FlowerDelivery\Repositories;

use Illuminate\Support\Collection;
use Plugins\Sirsoft\FlowerDelivery\Models\FlowerOption;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerOptionRepositoryInterface;

/**
 * 상품 맞춤 옵션 Repository 구현체
 */
class FlowerOptionRepository implements FlowerOptionRepositoryInterface
{
    /**
     * 상품의 활성 옵션을 그룹별로 조회합니다.
     *
     * @param int $productId 상품 ID
     * @param int|null $tenantId 테넌트 ID
     * @return Collection<int, FlowerOption>
     */
    public function getActiveByProduct(int $productId, ?int $tenantId = null): Collection
    {
        return FlowerOption::where('product_id', $productId)
            ->where('is_active', true)
            ->when($tenantId === null, fn ($q) => $q->whereNull('tenant_id'), fn ($q) => $q->where('tenant_id', $tenantId))
            ->orderBy('option_group')
            ->orderBy('sort_order')
            ->get(['id', 'product_id', 'option_group', 'option_key', 'label', 'price_delta', 'color_code']);
    }
}
