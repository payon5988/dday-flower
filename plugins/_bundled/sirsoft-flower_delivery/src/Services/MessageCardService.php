<?php

namespace Plugins\Sirsoft\FlowerDelivery\Services;

use Plugins\Sirsoft\FlowerDelivery\Models\FlowerMessageCard;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerMessageCardRepositoryInterface;

/**
 * 메시지 카드 서비스
 *
 * 주문에 연결된 감성 메시지 카드를 저장합니다.
 */
class MessageCardService
{
    /**
     * @param FlowerMessageCardRepositoryInterface $messageCardRepository 메시지 카드 Repository
     */
    public function __construct(
        private readonly FlowerMessageCardRepositoryInterface $messageCardRepository,
    ) {}

    /**
     * 메시지 카드를 저장합니다.
     *
     * @param int $orderId 주문 ID
     * @param string $sender 보내는 사람
     * @param string $recipient 받는 사람
     * @param string $content 메시지 본문 (encrypted 저장)
     * @return FlowerMessageCard 저장된 메시지 카드
     */
    public function storeCard(int $orderId, string $sender, string $recipient, string $content): FlowerMessageCard
    {
        return $this->messageCardRepository->store([
            'order_id' => $orderId,
            'encrypted_message' => $content,
            'sender_name' => $sender,
            'recipient_name' => $recipient,
        ]);
    }

    /**
     * 사용자의 메시지 카드 아카이브를 조회합니다 (본인 주문 기준).
     *
     * @param int $userId 사용자 ID
     * @return \Illuminate\Support\Collection<int, array{id: int, order_id: int, sender: string, recipient: string, content: string, created_at: string|null}>
     */
    public function archiveByUser(int $userId): \Illuminate\Support\Collection
    {
        $orderIds = \Modules\Sirsoft\Ecommerce\Models\Order::where('user_id', $userId)->pluck('id')->all();

        return $this->messageCardRepository->findByOrderIds($orderIds)
            ->map(fn (FlowerMessageCard $card) => [
                'id' => $card->id,
                'order_id' => $card->order_id,
                'sender' => $card->sender_name,
                'recipient' => $card->recipient_name,
                'content' => $card->encrypted_message,
                'created_at' => $card->created_at?->toDateTimeString(),
            ]);
    }

    /**
     * 메시지 카드 템플릿 8종을 반환합니다 (생일·감사·사랑·축하·결혼·근조·응원·자유작성).
     *
     * @return array<int, array{key: string, label: string, content: string}>
     */
    public function templates(): array
    {
        return [
            ['key' => 'birthday', 'label' => __('sirsoft-flower_delivery::messages.card_template_birthday_label'), 'content' => __('sirsoft-flower_delivery::messages.card_template_birthday')],
            ['key' => 'thanks', 'label' => __('sirsoft-flower_delivery::messages.card_template_thanks_label'), 'content' => __('sirsoft-flower_delivery::messages.card_template_thanks')],
            ['key' => 'love', 'label' => __('sirsoft-flower_delivery::messages.card_template_love_label'), 'content' => __('sirsoft-flower_delivery::messages.card_template_love')],
            ['key' => 'congrats', 'label' => __('sirsoft-flower_delivery::messages.card_template_congrats_label'), 'content' => __('sirsoft-flower_delivery::messages.card_template_congrats')],
            ['key' => 'wedding', 'label' => __('sirsoft-flower_delivery::messages.card_template_wedding_label'), 'content' => __('sirsoft-flower_delivery::messages.card_template_wedding')],
            ['key' => 'condolence', 'label' => __('sirsoft-flower_delivery::messages.card_template_condolence_label'), 'content' => __('sirsoft-flower_delivery::messages.card_template_condolence')],
            ['key' => 'cheer', 'label' => __('sirsoft-flower_delivery::messages.card_template_cheer_label'), 'content' => __('sirsoft-flower_delivery::messages.card_template_cheer')],
            ['key' => 'free', 'label' => __('sirsoft-flower_delivery::messages.card_template_free_label'), 'content' => ''],
        ];
    }
}
