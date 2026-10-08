# 데이터베이스 설계 명세서

## 3.1 핵심 확장 테이블 (g7_ 접두사 유지)
1. `g7_shop_delivery_slots`: 날짜, 시간대, 최대 주문 수량, 현재 예약 수량, 상태(오픈/마감).
2. `g7_shop_flower_options`: 상품별 맞춤 옵션 (리본 색상, 메시지 카드 문구, 보존제 처리 여부).
3. `g7_shop_subscriptions`: 정기 꽃배달 구독 주기(주간/월간), 다음 배송일, 결제 상태.

## 3.2 테넌시 준비 컬럼
- 향후 SaaS 확장을 대비해 `g7_shop_products`, `g7_shop_orders` 등에 `tenant_id` (nullable) 컬럼을 미리 추가하여 데이터 격리 구조 준비.

## 3.3 인덱싱 전략
- 배송 슬롯 조회 성능을 위해 `(product_id, delivery_date, time_slot)` 복합 인덱스 적용.

# 데이터베이스 스키마 명세서 (Laravel Migration 기준)

## 3.1 배송 슬롯 관리 테이블 (`g7_flower_delivery_slots`)
```php
Schema::create('g7_flower_delivery_slots', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('product_id')->index();
    $table->date('delivery_date')->index();
    $table->string('time_slot'); // 예: "14:00-16:00"
    $table->integer('max_capacity')->default(10); // 시간대별 최대 배송 건수
    $table->integer('current_bookings')->default(0);
    $table->boolean('is_active')->default(true);
    $table->unsignedBigInteger('tenant_id')->nullable()->index(); // SaaS 대비
    $table->timestamps();
    
    // 동시성 제어 및 조회 성능을 위한 복합 인덱스
    $table->unique(['product_id', 'delivery_date', 'time_slot', 'tenant_id']);
});

3.2 암호화 메시지 카드 테이블 (g7_flower_message_cards)
Schema::create('g7_flower_message_cards', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('order_id')->unique();
    $table->text('encrypted_message'); // Laravel의 $casts = ['encrypted_message' => 'encrypted'] 활용
    $table->string('sender_name', 50);
    $table->string('recipient_name', 50);
    $table->timestamps();
});