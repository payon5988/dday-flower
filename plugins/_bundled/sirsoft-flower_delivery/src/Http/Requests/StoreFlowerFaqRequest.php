<?php

namespace Plugins\Sirsoft\FlowerDelivery\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FAQ 저장 요청 검증 (생성·수정 공용)
 */
class StoreFlowerFaqRequest extends FormRequest
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
            'category' => ['sometimes', 'string', 'max:30'],
            'question' => ['required', 'array', 'min:1'],
            'question.*' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'array', 'min:1'],
            'answer.*' => ['required', 'string', 'max:2000'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
