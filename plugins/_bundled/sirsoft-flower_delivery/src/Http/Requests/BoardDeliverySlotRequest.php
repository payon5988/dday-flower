<?php

namespace Plugins\Sirsoft\FlowerDelivery\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 관리자 슬롯 현황판 조회 요청 검증
 */
class BoardDeliverySlotRequest extends FormRequest
{
    /**
     * 권한 확인 (라우트 permission 미들웨어가 담당).
     *
     * @return bool 항상 true
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 검증 규칙을 반환합니다.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date' => ['sometimes', 'date'],
            'product_id' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
