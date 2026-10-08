<?php

namespace Plugins\Sirsoft\FlowerDelivery\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\Sirsoft\FlowerDelivery\Exceptions\DeliverySlotFullException;
use Plugins\Sirsoft\FlowerDelivery\Models\FlowerDeliverySlot;
use Plugins\Sirsoft\FlowerDelivery\Services\DeliverySlotService;
use Plugins\Sirsoft\FlowerDelivery\Tests\PluginTestCase;

/**
 * 배송 슬롯 서비스 테스트
 */
class DeliverySlotServiceTest extends PluginTestCase
{
    use RefreshDatabase;

    /**
     * 잔여 수량 계산을 검증합니다.
     *
     * @return void
     */
    public function test_remaining_capacity_calculation(): void
    {
        $slot = new FlowerDeliverySlot([
            'max_capacity' => 10,
            'current_bookings' => 4,
        ]);

        $this->assertSame(6, $slot->remainingCapacity());
        $this->assertTrue($slot->isAvailable());
    }

    /**
     * 마감 슬롯 예약 시 예외 발생을 검증합니다.
     *
     * @return void
     */
    public function test_book_slot_throws_when_full(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__, 3).'/database/migrations');

        FlowerDeliverySlot::create([
            'product_id' => 1,
            'delivery_date' => now()->addDay()->toDateString(),
            'time_slot' => '14:00-16:00',
            'max_capacity' => 1,
            'current_bookings' => 1,
            'is_active' => true,
        ]);

        $service = app(DeliverySlotService::class);

        $this->expectException(DeliverySlotFullException::class);
        $service->bookSlot(1, now()->addDay()->toDateString(), '14:00-16:00');
    }

    /**
     * 정상 예약 시 예약 건수 증가를 검증합니다.
     *
     * @return void
     */
    public function test_book_slot_increments_bookings(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__, 3).'/database/migrations');

        $date = now()->addDay()->toDateString();

        FlowerDeliverySlot::create([
            'product_id' => 42,
            'delivery_date' => $date,
            'time_slot' => '10:00-12:00',
            'max_capacity' => 10,
            'current_bookings' => 0,
            'is_active' => true,
        ]);

        $service = app(DeliverySlotService::class);
        $result = $service->bookSlot(42, $date, '10:00-12:00');
        $slot = $result['slot'];

        $this->assertSame(1, $slot->current_bookings);
        $this->assertSame(9, $slot->remainingCapacity());
        $this->assertNull($result['reservation']);
    }

    /**
     * 당일 상태 계산을 검증합니다 (마감 전이면 당일배송 가능).
     *
     * @return void
     */
    public function test_day_status_before_cutoff(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__, 3).'/database/migrations');

        $today = now()->toDateString();

        FlowerDeliverySlot::create([
            'product_id' => 7,
            'delivery_date' => $today,
            'time_slot' => '10:00-12:00',
            'max_capacity' => 5,
            'current_bookings' => 2,
            'is_active' => true,
        ]);

        $service = app(DeliverySlotService::class);
        $status = $service->getDayStatus($today, 7);

        $this->assertSame($today, $status['date']);
        $this->assertTrue($status['is_today']);
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}$/', $status['cutoff']);
        // 마감 전/후에 따라 달라지므로 잔여와 정합만 검증
        $this->assertSame($status['same_day_available'], $status['same_day_remaining'] > 0);
    }

    /**
     * 예약 시 예약 행이 함께 생성되고 코드가 발급됨을 검증합니다.
     *
     * @return void
     */
    public function test_book_slot_creates_reservation_with_message(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__, 3).'/database/migrations');

        $date = now()->addDay()->toDateString();

        \Plugins\Sirsoft\FlowerDelivery\Models\FlowerDeliverySlot::create([
            'product_id' => 9,
            'delivery_date' => $date,
            'time_slot' => '10:00-12:00',
            'max_capacity' => 10,
            'current_bookings' => 0,
            'is_active' => true,
        ]);

        $user = \App\Models\User::factory()->create();

        $service = app(DeliverySlotService::class);
        $result = $service->bookSlot(9, $date, '10:00-12:00', null, $user->id, [
            'sender' => '김철수',
            'recipient' => '이영희',
            'content' => '생일 축하해',
        ]);

        $this->assertNotNull($result['reservation']);
        $this->assertMatchesRegularExpression('/^RSV-\d{8}-\d{4}$/', $result['reservation']->reservation_code);
        $this->assertSame('pending', $result['reservation']->status);
        $this->assertSame('생일 축하해', $result['reservation']->fresh()->encrypted_message);
    }

    /**
     * 예약 후 조회 캐시가 무효화됨을 검증합니다.
     *
     * @return void
     */
    public function test_book_slot_invalidates_list_cache(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__, 3).'/database/migrations');

        $date = now()->addDay()->toDateString();

        FlowerDeliverySlot::create([
            'product_id' => 11,
            'delivery_date' => $date,
            'time_slot' => '10:00-12:00',
            'max_capacity' => 10,
            'current_bookings' => 0,
            'is_active' => true,
        ]);

        $service = app(DeliverySlotService::class);

        $before = $service->getAvailableSlots(11, $date);
        $this->assertSame(10, $before[0]['remaining']);

        $service->bookSlot(11, $date, '10:00-12:00');

        $after = $service->getAvailableSlots(11, $date);
        $this->assertSame(9, $after[0]['remaining']);
    }

    /**
     * 리스너의 메모 파싱을 검증합니다.
     *
     * @return void
     */
    public function test_parse_memo_card(): void
    {
        $listener = app(\Plugins\Sirsoft\FlowerDelivery\Listeners\FlowerOrderAfterCreateListener::class);

        $card = $listener->parseMemoCard("[꽃배달 2026-10-04 14:00-16:00 RSV-20261004-0001]\n보내는 분: 김철수\n받는 분: 이영희\n메시지: 생일 축하해");

        $this->assertSame('김철수', $card['sender']);
        $this->assertSame('이영희', $card['recipient']);
        $this->assertSame('생일 축하해', $card['content']);
        $this->assertNull($listener->parseMemoCard('문 앞에 놓아주세요'));
    }
}
