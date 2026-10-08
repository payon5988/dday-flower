<?php

namespace Plugins\Sirsoft\FlowerDelivery\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 꽃배달 슬롯 예약 모델
 *
 * 슬롯 예약 시 생성되며 주문 생성 후 소진(consumed)됩니다.
 * 메시지 본문은 encrypted 캐스트로 암호화 저장됩니다.
 *
 * @property int $id
 * @property string $reservation_code
 * @property int $user_id
 * @property int $product_id
 * @property string $delivery_date
 * @property string $time_slot
 * @property string|null $encrypted_message
 * @property string|null $sender_name
 * @property string|null $recipient_name
 * @property string $status
 * @property int|null $consumed_order_id
 * @property int|null $tenant_id
 */
class FlowerReservation extends Model
{
    /**
     * 테이블명
     *
     * @var string
     */
    protected $table = 'g7_flower_reservations';

    /**
     * 대량 할당 허용 필드
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservation_code',
        'user_id',
        'product_id',
        'delivery_date',
        'time_slot',
        'encrypted_message',
        'sender_name',
        'recipient_name',
        'selected_options',
        'status',
        'consumed_order_id',
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
            'encrypted_message' => 'encrypted',
            'selected_options' => 'array',
        ];
    }

    /**
     * 예약자 관계
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
