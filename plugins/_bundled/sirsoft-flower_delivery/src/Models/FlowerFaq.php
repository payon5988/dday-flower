<?php

namespace Plugins\Sirsoft\FlowerDelivery\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 꽃배달 FAQ 모델 (관리자 관리)
 *
 * 질문·답변은 로케일별 JSON으로 보관합니다.
 *
 * @property int $id
 * @property string $category
 * @property array $question
 * @property array $answer
 * @property int $sort_order
 * @property bool $is_active
 * @property int|null $tenant_id
 */
class FlowerFaq extends Model
{
    /**
     * 테이블명
     *
     * @var string
     */
    protected $table = 'g7_flower_faqs';

    /**
     * 대량 할당 허용 필드
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category',
        'question',
        'answer',
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
            'question' => 'array',
            'answer' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * 로케일 텍스트를 해석합니다 (폴백: ko → 첫 값).
     *
     * @param array|string|null $map 로케일 맵
     * @param string $locale 로케일
     * @return string 해석된 텍스트
     */
    public static function localize(array|string|null $map, string $locale): string
    {
        if (is_string($map)) {
            return $map;
        }

        if (! is_array($map) || $map === []) {
            return '';
        }

        return (string) ($map[$locale] ?? $map['ko'] ?? reset($map));
    }
}
