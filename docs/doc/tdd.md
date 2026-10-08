# 테스트 주도 개발(TDD) 가이드

## 8.1 테스트 전략
- **단위 테스트 (PHPUnit 11.x)**: `DeliverySlotService`의 잔여 수량 계산 로직, 구독 주기 날짜 계산 로직 등 핵심 비즈니스 로직에 대해 90% 이상 커버리지 목표.
- **통합 테스트**: Laravel Sanctum 인증 하의 주문 생성 API 흐름 검증.

## 8.2 테스트 시나리오 예시
1. 배송 마감 시간을 초과한 시각에 동일일 배송 슬롯 요청 시 `ValidationException` 발생 검증.
2. 재고가 0인 슬롯에 대해 주문 시도 시 적절한 에러 메시지 반환 검증.

### 7. `tdd.md` & `harness.md` (품질 보증 자동화)
```markdown
# 테스트 주도 개발 및 자동화 하니스

## 7.1 핵심 동시성 테스트 (PHPUnit)
```php
public function test_concurrent_booking_does_not_exceed_capacity()
{
    $slot = DeliverySlot::factory()->create(['max_capacity' => 1, 'current_bookings' => 0]);
    
    // 두 개의 동시 요청 시뮬레이션
    $promise1 = fn() => $this->postJson('/api/v1/shop/delivery-slots/reserve', ['slot_id' => $slot->id]);
    $promise2 = fn() => $this->postJson('/api/v1/shop/delivery-slots/reserve', ['slot_id' => $slot->id]);
    
    $response1 = $promise1();
    $response2 = $promise2();

    $this->assertTrue($response1->successful());
    $this->assertEquals(422, $response2->status()); // 두 번째 요청은 실패해야 함
    $this->assertEquals(1, $slot->fresh()->current_bookings);
}

7.2 CI/CD 파이프라인 (GitHub Actions)
push 시: PHPStan (정적 분석), Laravel Pint (포맷팅), PHPUnit 실행.
보안 취약점 스캔: composer audit 및 프론트엔드 npm audit를 파이프라인에 포함하여 오픈소스 종속성 취약점 사전 차단.