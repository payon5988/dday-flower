<?php

namespace Plugins\Sirsoft\FlowerDelivery\Listeners;

use App\Contracts\Extension\HookListenerInterface;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerDeliverySlotRepositoryInterface;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerReservationRepositoryInterface;
use Plugins\Sirsoft\FlowerDelivery\Services\MessageCardService;

/**
 * 이커머스 주문 생성 후 꽃배달 정합성 리스너
 *
 * sirsoft-ecommerce.order.after_create 구독 — 주문에 담긴 꽃배달 정보를
 * 꽃배달 도메인에 반영합니다. 이커머스 코드는 한 줄도 수정하지 않습니다.
 *
 * 꽃 정보 전달 경로 (우선순위 순):
 * 1. 주문 속성 `flower_delivery.message_card` (향후 확장용 구조화 경로)
 * 2. 배송 메모(`shipping_memo`)에 합성된 꽃 블록 — 체크아웃 꽃 조각이
 *    `checkoutExtraPayload.shipping_memo` 로 실어 보낸 형식:
 *    [꽃배달 YYYY-MM-DD HH:MM-HH:MM RSV-xxx]
 *    보내는 분: xxx
 *    받는 분: xxx
 *    메시지: xxx
 *
 * 금전 이동이 아니므로 기본 큐 래핑을 유지합니다 (sync 불필요).
 */
class FlowerOrderAfterCreateListener implements HookListenerInterface
{
    /**
     * @param FlowerDeliverySlotRepositoryInterface $slotRepository 슬롯 Repository
     * @param FlowerReservationRepositoryInterface $reservationRepository 예약 Repository
     * @param MessageCardService $messageCardService 메시지 카드 서비스
     */
    public function __construct(
        private readonly FlowerDeliverySlotRepositoryInterface $slotRepository,
        private readonly FlowerReservationRepositoryInterface $reservationRepository,
        private readonly MessageCardService $messageCardService,
    ) {}

    /**
     * 구독할 훅 목록.
     *
     * @return array<string, array{method?: string, priority?: int, type?: string}>
     */
    public static function getSubscribedHooks(): array
    {
        return [
            'sirsoft-ecommerce.order.after_create' => [
                'method' => 'handleAfterCreate',
                'priority' => 20,
            ],
        ];
    }

    /**
     * 인터페이스 요구 메서드 (개별 메서드 사용 시 빈 구현).
     *
     * @param mixed ...$args
     * @return void
     */
    public function handle(...$args): void {}

    /**
     * 주문 생성 후 메시지 카드를 저장합니다.
     *
     * @param mixed $order 생성된 주문 (이커머스 Order 모델)
     * @return void
     */
    public function handleAfterCreate(mixed $order): void
    {
        if (! is_object($order) || ! isset($order->id)) {
            return;
        }

        if ($this->slotRepository instanceof FlowerDeliverySlotRepositoryInterface
            && $this->messageCardService instanceof MessageCardService) {
            $card = $this->extractCard($order);

            if ($card === null) {
                $card = $this->consumeReservation($order);
            }

            if ($card !== null && $card['content'] !== '') {
                $this->messageCardService->storeCard(
                    (int) $order->id,
                    (string) ($card['sender'] ?? ''),
                    (string) ($card['recipient'] ?? ''),
                    (string) $card['content'],
                );
            }
        }
    }

    /**
     * 주문자의 대기 예약을 소진하며 메시지 카드를 가져옵니다.
     *
     * 주문 상품과 같은 product_id 의 가장 오래된 대기 예약을 소진합니다.
     * 비회원 주문(user_id 없음)은 대상에서 제외합니다.
     *
     * @param mixed $order 생성된 주문
     * @return array{sender: string, recipient: string, content: string}|null
     */
    private function consumeReservation(mixed $order): ?array
    {
        $userId = (int) ($order->user_id ?? 0);

        if ($userId <= 0) {
            return null;
        }

        $productIds = [];
        try {
            foreach ($order->options ?? [] as $option) {
                if (isset($option->product_id)) {
                    $productIds[] = (int) $option->product_id;
                }
            }
        } catch (\Throwable) {
            return null;
        }

        $reservations = $this->reservationRepository->findPendingByUser($userId, array_values(array_unique($productIds)));

        foreach ($reservations as $reservation) {
            $this->reservationRepository->markConsumed($reservation, (int) $order->id);

            $content = (string) ($reservation->encrypted_message ?? '');

            if ($content !== '') {
                return [
                    'sender' => (string) ($reservation->sender_name ?? ''),
                    'recipient' => (string) ($reservation->recipient_name ?? ''),
                    'content' => $content,
                ];
            }

            return null;
        }

        return null;
    }

    /**
     * 주문에서 메시지 카드를 추출합니다 (구조화 속성 우선, 배송 메모 파싱 대체).
     *
     * @param mixed $order 생성된 주문
     * @return array{sender: string, recipient: string, content: string}|null
     */
    private function extractCard(mixed $order): ?array
    {
        $memo = (array) ($order->flower_delivery ?? []);

        if (isset($memo['message_card'])) {
            $card = (array) $memo['message_card'];

            if (($card['content'] ?? '') !== '') {
                return [
                    'sender' => (string) ($card['sender'] ?? ''),
                    'recipient' => (string) ($card['recipient'] ?? ''),
                    'content' => (string) $card['content'],
                ];
            }
        }

        return $this->parseMemoCard((string) ($order->shipping_memo ?? ''));
    }

    /**
     * 배송 메모에서 꽃 블록을 파싱합니다.
     *
     * 형식:
     * [꽃배달 YYYY-MM-DD HH:MM-HH:MM RSV-xxx]
     * 보내는 분: xxx
     * 받는 분: xxx
     * 메시지: xxx (뒷부분 전체)
     *
     * @param string $memo 배송 메모
     * @return array{sender: string, recipient: string, content: string}|null
     */
    public function parseMemoCard(string $memo): ?array
    {
        if (! preg_match('/\[꽃배달 (\d{4}-\d{2}-\d{2}) ([0-9:]+-[0-9:]+) (RSV-[\w-]+)\]/u', $memo, $m)) {
            return null;
        }

        $tail = mb_substr($memo, mb_strpos($memo, $m[0]) + mb_strlen($m[0]));
        $lines = preg_split('/\r?\n/', trim($tail));
        $sender = '';
        $recipient = '';
        $messageLines = [];

        foreach ($lines as $line) {
            if (str_starts_with($line, '보내는 분:')) {
                $sender = trim(mb_substr($line, mb_strlen('보내는 분:')));
            } elseif (str_starts_with($line, '받는 분:')) {
                $recipient = trim(mb_substr($line, mb_strlen('받는 분:')));
            } elseif (str_starts_with($line, '메시지:')) {
                $messageLines[] = trim(mb_substr($line, mb_strlen('메시지:')));
            } elseif ($messageLines !== []) {
                $messageLines[] = $line;
            }
        }

        $content = trim(implode("\n", $messageLines));

        if ($content === '') {
            return null;
        }

        return ['sender' => $sender, 'recipient' => $recipient, 'content' => $content];
    }
}
