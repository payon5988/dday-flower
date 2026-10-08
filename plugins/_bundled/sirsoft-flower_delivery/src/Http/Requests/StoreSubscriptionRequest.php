<?php

namespace Plugins\Sirsoft\FlowerDelivery\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Plugins\Sirsoft\FlowerDelivery\Enums\SubscriptionCycle;

/**
 * 정기구독 신청 요청 검증
 */
class StoreSubscriptionRequest extends FormRequest
{
    /**
     * 권한 확인 (인증은 라우트 미들웨어가 담당).
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
            'product_id' => ['required', 'integer', 'min:1'],
            'cycle' => ['required', 'string', Rule::in(array_map(fn (SubscriptionCycle $c) => $c->value, SubscriptionCycle::cases()))],
        ];
    }
}
