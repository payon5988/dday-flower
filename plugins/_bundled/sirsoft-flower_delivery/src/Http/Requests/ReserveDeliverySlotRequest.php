<?php

namespace Plugins\Sirsoft\FlowerDelivery\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 배송 슬롯 예약 요청 검증
 */
class ReserveDeliverySlotRequest extends FormRequest
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
     * 메시지 카드는 전부 비워 두면 미첨부로 처리됩니다 (부분 입력은 200자·허용문자만 검증).
     * 경조사 문구의 한자(謹弔 등)·중점(·)·괄호·빗금·물결도 허용합니다.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'min:1'],
            'delivery_date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot' => ['required', 'string', 'max:30'],
            'message_card' => ['nullable', 'array'],
            'message_card.sender' => ['nullable', 'string', 'max:50'],
            'message_card.recipient' => ['nullable', 'string', 'max:50'],
            'message_card.content' => ['nullable', 'string', 'max:200', 'regex:/^[a-zA-Z0-9가-힣\x{4E00}-\x{9FFF}\s\.\,\!\?·\(\)\/~]+$/u'],
            'selected_options' => ['nullable', 'array', 'max:20'],
            'selected_options.*.group' => ['nullable', 'string', 'max:30'],
            'selected_options.*.key' => ['nullable', 'string', 'max:50'],
            'selected_options.*.label' => ['nullable', 'string', 'max:100'],
            'selected_options.*.price_delta' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'tenant_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * 검증 메시지를 반환합니다.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message_card.content.regex' => __('sirsoft-flower_delivery::messages.message_card_invalid_chars'),
        ];
    }
}
