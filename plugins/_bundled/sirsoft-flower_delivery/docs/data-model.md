# 꽃배달 확장 (Flower Delivery) — 데이터 모델

> 모델·소유 테이블·마이그레이션·Enum · 진입점: [AGENTS.md](../AGENTS.md)

## 모델

<!-- @generated:models START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 모델 | 테이블 | fillable | 관계 | 특성 |
|---|---|---|---|---|
| `FlowerDeliverySlot` | `g7_flower_delivery_slots` | 7 | - | - |
| `FlowerFaq` | `g7_flower_faqs` | 6 | - | - |
| `FlowerMessageCard` | `g7_flower_message_cards` | 4 | - | - |
| `FlowerOption` | `g7_flower_options` | 9 | - | - |
| `FlowerReservation` | `g7_flower_reservations` | 12 | user→User | - |
| `FlowerSubscription` | `g7_flower_subscriptions` | 6 | user→User | - |
<!-- @generated:models END -->

<!-- @intent START -->
세 모델은 서로 FK 로 묶지 않습니다. 이커머스 상품·주문은 `product_id`·`order_id` 값으로만 참조합니다 — 모듈이 없을 때 플러그인 마이그레이션이 깨지지 않게 하기 위함입니다. `FlowerSubscription.user` 만 코어 `User` 를 직접 belongsTo 합니다 (회원 없이는 구독이 성립하지 않으므로).
<!-- @intent END -->

## 소유 테이블

<!-- @generated:tables START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 테이블 | 모델 |
|---|---|
| `g7_flower_delivery_slots` | `FlowerDeliverySlot` |
| `g7_flower_faqs` | `FlowerFaq` |
| `g7_flower_message_cards` | `FlowerMessageCard` |
| `g7_flower_options` | `FlowerOption` |
| `g7_flower_reservations` | `FlowerReservation` |
| `g7_flower_subscriptions` | `FlowerSubscription` |
<!-- @generated:tables END -->

<!-- @intent START -->
테이블명은 `g7_` 접두사를 유지하되 기존 코어·이커머스 테이블은 건드리지 않습니다. 슬롯 테이블의 `(product_id, delivery_date, time_slot, tenant_id)` 유니크가 동시 예약의 중복 행 생성을 DB 차원에서 막고, `tenant_id` nullable 이 2단계 SaaS 전환 시 데이터 이관 없이 스코프를 붙일 자리입니다.
<!-- @intent END -->

## 마이그레이션

<!-- @generated:migrations START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
마이그레이션 7개.

| 파일 | 생성 테이블 | 변경 테이블 | down() |
|---|---|---|---|
| `2026_10_03_000001_create_g7_flower_delivery_slots_table.php` | `g7_flower_delivery_slots` | `g7_flower_delivery_slots` | ✅ |
| `2026_10_03_000002_create_g7_flower_message_cards_table.php` | `g7_flower_message_cards` | `g7_flower_message_cards` | ✅ |
| `2026_10_03_000003_create_g7_flower_subscriptions_table.php` | `g7_flower_subscriptions` | `g7_flower_subscriptions` | ✅ |
| `2026_10_04_000004_create_g7_flower_options_table.php` | `g7_flower_options` | `g7_flower_options` | ✅ |
| `2026_10_04_000005_create_g7_flower_reservations_table.php` | `g7_flower_reservations` | `g7_flower_reservations` | ✅ |
| `2026_10_04_000006_add_options_to_reservations_table.php` | - | `g7_flower_reservations` | ✅ |
| `2026_10_05_000007_create_g7_flower_faqs_table.php` | `g7_flower_faqs` | `g7_flower_faqs` | ✅ |
<!-- @generated:migrations END -->

<!-- @intent START -->
신규 테이블 생성만 하며 기존 테이블 변경은 없습니다. 슬롯 조회 성능을 위해 `(product_id, delivery_date, time_slot, tenant_id)` 복합 유니크를 마이그레이션에서 함께 겁니다. 기설치본 백필이 필요해지면 `upgrades/` 스텝을 추가합니다 (현재 해당 없음).
<!-- @intent END -->

## Enum

<!-- @generated:enums START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| Enum | backing | case 수 | case |
|---|---|---|---|
| `SubscriptionCycle` | `string` | 2 | `weekly`, `monthly` |
| `SubscriptionStatus` | `string` | 3 | `active`, `paused`, `cancelled` |
<!-- @generated:enums END -->

<!-- @intent START -->
구독 상태·주기는 문자열 리터럴 대신 Enum 이 SSoT 입니다. 새 상태(예: `expired`)를 추가하면 `FlowerSubscriptionRepository::updateStatus()` 호출부와 구독 화면의 상태 분기를 함께 확인합니다.
<!-- @intent END -->

## Repository

<!-- @generated:repositories START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 클래스 | 종류 | 설명 |
|---|---|---|
| `FlowerDeliverySlotRepository` | 구현 | 배송 슬롯 Repository 구현체 |
| `FlowerDeliverySlotRepositoryInterface` | 인터페이스 | 배송 슬롯 Repository 인터페이스 |
| `FlowerFaqRepository` | 구현 | FAQ Repository 구현체 |
| `FlowerFaqRepositoryInterface` | 인터페이스 | FAQ Repository 인터페이스 |
| `FlowerMessageCardRepository` | 구현 | 메시지 카드 Repository 구현체 |
| `FlowerMessageCardRepositoryInterface` | 인터페이스 | 메시지 카드 Repository 인터페이스 |
| `FlowerOptionRepository` | 구현 | 상품 맞춤 옵션 Repository 구현체 |
| `FlowerOptionRepositoryInterface` | 인터페이스 | 상품 맞춤 옵션 Repository 인터페이스 |
| `FlowerReservationRepository` | 구현 | 슬롯 예약 Repository 구현체 |
| `FlowerReservationRepositoryInterface` | 인터페이스 | 슬롯 예약 Repository 인터페이스 |
| `FlowerSubscriptionRepository` | 구현 | 정기구독 Repository 구현체 |
| `FlowerSubscriptionRepositoryInterface` | 인터페이스 | 정기구독 Repository 인터페이스 |
<!-- @generated:repositories END -->

<!-- @intent START -->
Service 는 인터페이스만 주입받으며 `FlowerDeliveryServiceProvider` 가 구현체를 바인딩합니다. 동시성 예약(`bookSlot`)은 Repository 를 거치지 않고 모델에 `lockForUpdate()` 를 겁니다 — 잠금 행 선택과 increment 가 같은 트랜잭션에 있어야 하므로 Service 가 직접 소유합니다. 목록 조회는 Repository 의 컬럼 프루닝 쿼리를 씁니다.
<!-- @intent END -->
