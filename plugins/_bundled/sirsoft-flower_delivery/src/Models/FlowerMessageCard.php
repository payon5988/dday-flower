<?php

namespace Plugins\Sirsoft\FlowerDelivery\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 꽃배달 메시지 카드 모델
 *
 * 메시지 본문은 encrypted 캐스트로 AES 암호화 저장됩니다 (PIPA 준수).
 *
 * @property int $id
 * @property int $order_id
 * @property string $encrypted_message
 * @property string $sender_name
 * @property string $recipient_name
 */
class FlowerMessageCard extends Model
{
    /**
     * 테이블명
     *
     * @var string
     */
    protected $table = 'g7_flower_message_cards';

    /**
     * 대량 할당 허용 필드
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'encrypted_message',
        'sender_name',
        'recipient_name',
    ];

    /**
     * 속성 캐스팅 정의
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'encrypted_message' => 'encrypted',
        ];
    }
}
