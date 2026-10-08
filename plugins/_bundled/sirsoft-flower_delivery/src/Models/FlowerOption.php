<?php

namespace Plugins\Sirsoft\FlowerDelivery\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 꽃배달 상품 맞춤 옵션 모델
 *
 * 리본 색상·포장 스타일·보존제 처리·추가 상품을 상품별로 관리합니다.
 *
 * @property int $id
 * @property int $product_id
 * @property string $option_group
 * @property string $option_key
 * @property string $label
 * @property int $price_delta
 * @property string|null $color_code
 * @property int $sort_order
 * @property bool $is_active
 * @property int|null $tenant_id
 */
class FlowerOption extends Model
{
    /**
     * 테이블명
     *
     * @var string
     */
    protected $table = 'g7_flower_options';

    /**
     * 대량 할당 허용 필드
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'option_group',
        'option_key',
        'label',
        'price_delta',
        'color_code',
        'sort_order',
        'is_active',
        'tenant_id',
    ];

    /**
     * 속성 캐스팅 정의
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_delta' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
