<?php

namespace Plugins\Sirsoft\FlowerDelivery\Services;

use App\Contracts\Extension\CacheInterface;
use App\Services\PluginSettingsService;
use Illuminate\Support\Facades\DB;
use Plugins\Sirsoft\FlowerDelivery\Exceptions\DeliverySlotFullException;
use Plugins\Sirsoft\FlowerDelivery\Models\FlowerDeliverySlot;
use Plugins\Sirsoft\FlowerDelivery\Models\FlowerReservation;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerDeliverySlotRepositoryInterface;
use Plugins\Sirsoft\FlowerDelivery\Repositories\Contracts\FlowerReservationRepositoryInterface;

/**
 * 배송 슬롯 서비스
 *
 * 동일일 배송 마감 계산 + 동시성 안전 예약 (lockForUpdate)을 제공합니다.
 */
class DeliverySlotService
{
    /**
     * 플러그인 식별자
     */
    private const PLUGIN_ID = 'sirsoft-flower_delivery';

    /**
     * @param FlowerDeliverySlotRepositoryInterface $slotRepository 슬롯 Repository
     * @param FlowerReservationRepositoryInterface $reservationRepository 예약 Repository
     * @param PluginSettingsService $pluginSettings 플러그인 설정 서비스
     * @param CacheInterface $cache 캐시 드라이버 (file/database/redis — 환경 설정に従う)
     */
    public function __construct(
        private readonly FlowerDeliverySlotRepositoryInterface $slotRepository,
        private readonly FlowerReservationRepositoryInterface $reservationRepository,
        private readonly PluginSettingsService $pluginSettings,
        private readonly CacheInterface $cache,
    ) {}

    /**
     * 당일배송 마감 시각(HH:MM)을 반환합니다.
     *
     * @return string 마감 시각
     */
    public function getCutoff(): string
    {
        $cutoff = $this->pluginSettings->get(self::PLUGIN_ID, 'same_day_cutoff', '14:00');

        return is_string($cutoff) && preg_match('/^\d{2}:\d{2}$/', $cutoff) ? $cutoff : '14:00';
    }

    /**
     * 특정 날짜의 당일배송 가능 상태를 계산합니다.
     *
     * 오늘이고 현재 시각이 마감 시각을 넘었으면 당일배송 불가입니다.
     * 오늘이 아니면 마감과 무관하게 예약 가능합니다.
     *
     * @param string $date 배송일 (Y-m-d)
     * @param int $productId 상품 ID
     * @param int|null $tenantId 테넌트 ID
     * @return array{date: string, cutoff: string, is_today: bool, same_day_available: bool, same_day_remaining: int}
     */
    public function getDayStatus(string $date, int $productId, ?int $tenantId = null): array
    {
        /** @var array $status */
        $status = $this->cache->remember(
            $this->cacheKey('day', $productId, $date, $tenantId),
            fn () => $this->computeDayStatus($date, $productId, $tenantId),
            $this->getCacheTtl(),
        );

        return $status;
    }

    /**
     * 특정 날짜의 당일배송 가능 상태를 계산합니다 (캐시 미적중 시).
     *
     * 오늘이고 현재 시각이 마감 시각을 넘었으면 당일배송 불가입니다.
     * 오늘이 아니면 마감과 무관하게 예약 가능합니다.
     *
     * @param string $date 배송일 (Y-m-d)
     * @param int $productId 상품 ID
     * @param int|null $tenantId 테넌트 ID
     * @return array{date: string, cutoff: string, is_today: bool, same_day_available: bool, same_day_remaining: int}
     */
    private function computeDayStatus(string $date, int $productId, ?int $tenantId = null): array
    {
        $cutoff = $this->getCutoff();
        $isToday = $date === now()->toDateString();
        $pastCutoff = $isToday && now()->format('H:i') >= $cutoff;

        $remaining = 0;
        if (! $pastCutoff) {
            foreach ($this->slotRepository->getByProductAndDate($productId, $date, $tenantId) as $slot) {
                $remaining += $slot->remainingCapacity();
            }
        }

        return [
            'date' => $date,
            'cutoff' => $cutoff,
            'is_today' => $isToday,
            'same_day_available' => ! $pastCutoff && $remaining > 0,
            'same_day_remaining' => $remaining,
        ];
    }

    /**
     * 슬롯 조회 캐시 TTL(초)을 반환합니다.
     *
     * @return int TTL (기본 300초)
     */
    public function getCacheTtl(): int
    {
        $ttl = (int) $this->pluginSettings->get(self::PLUGIN_ID, 'slot_cache_ttl', 300);

        return $ttl > 0 ? $ttl : 300;
    }

    /**
     * 슬롯 캐시 키를 생성합니다.
     *
     * @param string $scope 범위 (list/day)
     * @param int $productId 상품 ID
     * @param string $date 배송일 (Y-m-d)
     * @param int|null $tenantId 테넌트 ID
     * @return string 캐시 키
     */
    private function cacheKey(string $scope, int $productId, string $date, ?int $tenantId): string
    {
        return sprintf('flower:slots:%s:%s:%d:%s', $scope, $tenantId ?? 'shared', $productId, $date);
    }

    /**
     * 상품·날짜의 슬롯 캐시를 무효화합니다 (예약·관리자 변경 시 호출).
     *
     * @param int $productId 상품 ID
     * @param string $date 배송일 (Y-m-d)
     * @param int|null $tenantId 테넌트 ID
     * @return void
     */
    public function invalidateSlotCache(int $productId, string $date, ?int $tenantId = null): void
    {
        $this->cache->forget($this->cacheKey('list', $productId, $date, $tenantId));
        $this->cache->forget($this->cacheKey('day', $productId, $date, $tenantId));
    }

    /**
     * 슬롯을 예약합니다 (동시성 안전).
     *
     * DB 트랜잭션 + lockForUpdate 로 이중 예약을 원천 차단하고,
     * 같은 트랜잭션에서 예약 행을 생성합니다 (주문 연결용).
     *
     * @param int $productId 상품 ID
     * @param string $date 배송일 (Y-m-d)
     * @param string $timeSlot 시간대 (예: 14:00-16:00)
     * @param int|null $tenantId 테넌트 ID
     * @param int|null $userId 예약자 ID (null 이면 예약 행 미생성)
     * @param array{sender?: string, recipient?: string, content?: string}|null $message 메시지 카드 (content 없으면 미저장)
     * @param array<int, array{group?: string, key?: string, label?: string, price_delta?: int}>|null $options 선택 맞춤 옵션 스냅샷
     * @return array{slot: FlowerDeliverySlot, reservation: FlowerReservation|null}
     * @throws DeliverySlotFullException 슬롯 마감 시
     */
    public function bookSlot(int $productId, string $date, string $timeSlot, ?int $tenantId = null, ?int $userId = null, ?array $message = null, ?array $options = null): array
    {
        return DB::transaction(function () use ($productId, $date, $timeSlot, $tenantId, $userId, $message, $options) {
            $query = FlowerDeliverySlot::where('product_id', $productId)
                ->whereDate('delivery_date', $date)
                ->where('time_slot', $timeSlot);

            $query = $tenantId === null
                ? $query->whereNull('tenant_id')
                : $query->where('tenant_id', $tenantId);

            /** @var FlowerDeliverySlot|null $slot */
            $slot = $query->lockForUpdate()->first();

            if ($slot === null || ! $slot->is_active) {
                throw new DeliverySlotFullException('sirsoft-flower_delivery::messages.slot_closed');
            }

            if ($slot->current_bookings >= $slot->max_capacity) {
                throw new DeliverySlotFullException();
            }

            $slot->increment('current_bookings');
            $slot = $slot->fresh();

            // 잔여가 바뀌었으므로 조회 캐시를 즉시 무효화한다 (매진 표시 지연 방지).
            $this->invalidateSlotCache($productId, $date, $tenantId);

            $reservation = null;

            if ($userId !== null) {
                $content = trim((string) ($message['content'] ?? ''));
                $cleanOptions = [];

                foreach ((array) ($options ?? []) as $opt) {
                    if (! is_array($opt)) {
                        continue;
                    }
                    $cleanOptions[] = [
                        'group' => substr((string) ($opt['group'] ?? ''), 0, 30),
                        'key' => substr((string) ($opt['key'] ?? ''), 0, 50),
                        'label' => substr((string) ($opt['label'] ?? ''), 0, 100),
                        'price_delta' => (int) ($opt['price_delta'] ?? 0),
                    ];
                }

                // 예약 코드는 행 ID 에서 확정 — 동시 예약의 채번 경합을 원천 차단한다.
                // 플레이스홀더에도 고유값을 넣어 유니크 충돌을 방지한다.
                $reservation = $this->reservationRepository->create([
                    'reservation_code' => 'RSV-TMP-'.\Illuminate\Support\Str::random(12),
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'delivery_date' => $date,
                    'time_slot' => $timeSlot,
                    'encrypted_message' => $content !== '' ? $content : null,
                    'sender_name' => $content !== '' ? substr((string) ($message['sender'] ?? ''), 0, 50) : null,
                    'recipient_name' => $content !== '' ? substr((string) ($message['recipient'] ?? ''), 0, 50) : null,
                    'selected_options' => $cleanOptions !== [] ? $cleanOptions : null,
                    'status' => 'pending',
                    'tenant_id' => $tenantId,
                ]);
                $reservation->reservation_code = sprintf('RSV-%s-%04d', now()->format('Ymd'), $reservation->id);
                $reservation->save();
                $reservation = $reservation->fresh();
            }

            return ['slot' => $slot, 'reservation' => $reservation];
        });
    }

    /**
     * 상품·날짜 기준 슬롯 목록을 조회합니다 (Redis 캐시 대상, TTL 5분 권장).
     *
     * @param int $productId 상품 ID
     * @param string $date 배송일 (Y-m-d)
     * @param int|null $tenantId 테넌트 ID
     * @return array<int, array{time: string, available: bool, remaining: int}>
     */
    public function getAvailableSlots(int $productId, string $date, ?int $tenantId = null): array
    {
        $key = $this->cacheKey('list', $productId, $date, $tenantId);

        /** @var array|null $cached */
        $cached = $this->cache->get($key);

        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        $slots = $this->slotRepository->getByProductAndDate($productId, $date, $tenantId)
            ->map(fn (FlowerDeliverySlot $slot) => [
                'time' => $slot->time_slot,
                'available' => $slot->isAvailable(),
                'remaining' => $slot->remainingCapacity(),
            ])
            ->all();

        // 빈 결과는 캐시하지 않는다 — 슬롯 신규 등록 즉시 노출 (생성 경로는 무효화 없음).
        if ($slots !== []) {
            $this->cache->put($key, $slots, $this->getCacheTtl());
        }

        return $slots;
    }
}
