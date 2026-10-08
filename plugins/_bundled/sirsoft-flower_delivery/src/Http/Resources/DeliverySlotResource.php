<?php

namespace Plugins\Sirsoft\FlowerDelivery\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 배송 슬롯 리소스
 *
 * 목록 응답은 화면이 실제로 그리는 것만 싣습니다.
 */
class DeliverySlotResource extends JsonResource
{
    /**
     * 리소스를 배열로 변환합니다.
     *
     * @param Request $request 요청
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'time' => $this->resource['time'] ?? '',
            'available' => (bool) ($this->resource['available'] ?? false),
            'remaining' => (int) ($this->resource['remaining'] ?? 0),
        ];
    }
}
