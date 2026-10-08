<?php

namespace Plugins\Sirsoft\FlowerDelivery\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 꽃배달 정기구독 모델
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property string $cycle
 * @property string|null $next_delivery_date
 * @property string $status
 * @property int|null $tenant_id
 */
class FlowerSubscription extends Model
{
    /**
     * 테이블명
     *
     * @var string
     */
    protected $table = 'g7_flower_subscriptions';

    /**
     * 대량 할당 허용 필드
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'cycle',
        'next_delivery_date',
        'status',
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
            'next_delivery_date' => 'date',
        ];
    }

    /**
     * 구독자 관계
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
