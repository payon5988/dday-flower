<?php

namespace Plugins\Sirsoft\FlowerDelivery\Exceptions;

use Exception;

/**
 * 배송 슬롯 마감 예외
 *
 * 잔여 수량이 없을 때 예약 시도를 차단합니다.
 * 컨트롤러는 typed catch 로 받아 422 로 매핑합니다 (기존 상태코드 유지).
 */
class DeliverySlotFullException extends Exception
{
    /**
     * @param string $messageKey 번역 키
     * @param array $messageParams 번역 치환 파라미터
     */
    public function __construct(
        private readonly string $messageKey = 'sirsoft-flower_delivery::messages.slot_full',
        private readonly array $messageParams = [],
    ) {
        parent::__construct($messageKey);
    }

    /**
     * 번역 메시지 키를 반환합니다.
     *
     * @return string 메시지 키
     */
    public function getMessageKey(): string
    {
        return $this->messageKey;
    }

    /**
     * 번역 치환 파라미터를 반환합니다.
     *
     * @return array 치환 파라미터
     */
    public function getMessageParams(): array
    {
        return $this->messageParams;
    }
}
