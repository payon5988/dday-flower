<?php

namespace Plugins\Sirsoft\FlowerDelivery\Services;

use App\Extension\HookManager;
use Modules\Sirsoft\Ecommerce\Models\Product;

/**
 * 감사 메시지·SNS 공유 서비스
 *
 * 주문 완료 시 감사 메시지와 SNS 공유 페이로드를 생성합니다.
 * 문구는 규칙 기반 템플릿이며, `sirsoft-flower_delivery.share.message` Filter 훅으로
 * AI 플러그인이 덮어쓸 수 있습니다 (AI-ready 확장점).
 */
class ShareService
{
    /**
     * 상품 공유 페이로드를 생성합니다.
     *
     * @param int $productId 상품 ID
     * @return array{product_id: int, name: string, price: string, image: string|null, url: string, message: string}|null 상품이 없으면 null
     */
    public function buildProductShare(int $productId): ?array
    {
        /** @var Product|null $product */
        $product = Product::with('images')->find($productId);

        if ($product === null) {
            return null;
        }

        $name = is_array($product->name) ? ($product->name['ko'] ?? reset($product->name)) : (string) $product->name;

        $payload = [
            'product_id' => $product->id,
            'name' => $name,
            'price' => (string) $product->selling_price,
            'image' => $product->getThumbnailUrl(),
            'url' => "/shop/products/{$product->product_code}",
            'message' => __('sirsoft-flower_delivery::messages.share_default', ['name' => $name]),
        ];

        /** @var array $filtered */
        $filtered = HookManager::applyFilters('sirsoft-flower_delivery.share.message', $payload, $product);

        return is_array($filtered) ? $filtered : $payload;
    }
}
