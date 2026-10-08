<?php

namespace Plugins\Sirsoft\FlowerDelivery\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 꽃배달 배송 슬롯 모델
 *
 * 상품별 배송 가능 일자/시간대 매트릭스를 관리합니다.
 * 기존 그누보드7·이커머스 테이블을 건드리지 않고 독립 테이블만 사용합니다.
 *
 * @property int $id
 * @property int $product_id
 * @property string $delivery_date
 * @property string $time_slot
 * @property int $max_capacity
 * @property int $current_bookings
 * @property bool $is_active
 * @property int|null $tenant_id
 */
class FlowerDeliverySlot extends Model
{
    /**
     * 테이블명
     *
     * @var string
     */
    protected $table = 'g7_flower_delivery_slots';

    /**
     * 대량 할당 허용 필드
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'delivery_date',
        'time_slot',
        'max_capacity',
        'current_bookings',
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
            'delivery_date' => 'date',
            'max_capacity' => 'integer',
            'current_bookings' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * 잔여 수량을 계산합니다.
     *
     * @return int 잔여 수량
     */
    public function remainingCapacity(): int
    {
        return max(0, $this->max_capacity - $this->current_bookings);
    }

    /**
     * 예약 가능 여부를 반환합니다.
     *
     * @return bool 예약 가능 여부
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->remainingCapacity() > 0;
    }
}
