<?php

namespace Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts;

use Illuminate\Support\Collection;
use Plugins\Sirsoft\FlowerDelivery\Models\FlowerOption;

/**
 * 상품 맞춤 옵션 Repository 인터페이스
 */
interface FlowerOptionRepositoryInterface
{
    /**
     * 상품의 활성 옵션을 그룹별로 조회합니다.
     *
     * @param int $productId 상품 ID
     * @param int|null $tenantId 테넌트 ID
     * @return Collection<int, FlowerOption>
     */
    public function getActiveByProduct(int $productId, ?int $tenantId = null): Collection;
}
