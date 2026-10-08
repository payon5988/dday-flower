<?php

namespace Plugins\Sirsoft\FlowerDelivery\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 배송 슬롯 조회 요청 검증
 */
class IndexDeliverySlotRequest extends FormRequest
{
    /**
     * 권한 확인 (공개 조회 허용).
     *
     * @return bool 항상 true
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 검증 규칙을 반환합니다 (date 미지정 시 내일 날짜가 기본 적용됨).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date' => ['sometimes', 'date', 'after_or_equal:today'],
            'product_id' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
