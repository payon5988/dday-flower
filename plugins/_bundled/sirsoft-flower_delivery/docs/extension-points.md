# 꽃배달 확장 (Flower Delivery) — 확장점

> 발행/구독 훅·미들웨어·채널·스케줄 · 진입점: [AGENTS.md](../AGENTS.md)

## 발행 훅

<!-- @generated:hooks-published START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
발행 훅 3종 / 호출 지점 2곳. 이 중 1종은 `getHooks()` 선언에 없어 소스에서 자동 감지한 것입니다 — 선언에 추가하면 유형과 설명이 함께 실립니다.

| 훅 이름 | 유형 | 설명 | 발행 위치 |
|---|---|---|---|
| `sirsoft-flower_delivery.options.listed` | action | — | `src/Services/FlowerOptionService.php:40` |
| `sirsoft-flower_delivery.share.message` | filter | SNS 공유 문구 생성 시 발화 (AI 플러그인이 덮어쓰는 확장점) | `src/Services/ShareService.php:44` |
| `sirsoft-flower_delivery.slot.reserved` | action | 배송 슬롯 예약 완료 시 발화 | 선언 (호출 위치 미확인) |
<!-- @generated:hooks-published END -->

<!-- @intent START -->
`sirsoft-flower_delivery.slot.reserved` 는 선언만 있고 아직 `doAction` 호출 지점이 없습니다. 예약 확정 후 외부 확장이 반응해야 할 때 `DeliverySlotService::bookSlot()` 커밋 직후에 발행을 추가하고 이 절을 갱신합니다. 그 전까지 이 훅을 구독해도 호출되지 않습니다.
<!-- @intent END -->

## 구독 훅

<!-- @generated:hooks-subscribed START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 훅 이름 | 유형 | 리스너 | 메서드 | 우선순위 |
|---|---|---|---|---|
| `sirsoft-ecommerce.order.after_create` | action (미선언) | `FlowerOrderAfterCreateListener` | `handleAfterCreate` | 20 |
<!-- @generated:hooks-subscribed END -->

<!-- @intent START -->
주문 생성 후 메시지 카드를 저장하는 유일한 접점입니다. 이커머스 코드를 고치지 않고 주문 흐름에 붙는 법이 이것이며, 구독 대상 훅 이름이 바뀌면 이 리스너는 조용히 아무 일도 하지 않게 되므로 이커머스 업그레이드 시 확인합니다.
<!-- @intent END -->

## 훅 리스너

<!-- @generated:listeners START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 리스너 | 구독 훅 | 등록 방식 | HookListenerInterface | 파일 |
|---|---|---|---|---|
| `FlowerOrderAfterCreateListener` | 1개 | 명시 등록 | ✅ | `src/Listeners/FlowerOrderAfterCreateListener.php` |
<!-- @generated:listeners END -->

<!-- @intent START -->
리스너는 Repository 경유로 데이터를 다룹니다(`Model::query()`·`DB::table()` 직접 호출 금지). 부가 동작이므로 설정 토글 뒤에 두는 것이 규약이나, 현재 리스너는 메시지 카드가 있을 때만 저장하므로 별도 토글이 없습니다.
<!-- @intent END -->

## 레이아웃 확장

<!-- @generated:layout-extensions START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 대상 | 설명 |
|---|---|
| `resources/extensions/checkout-delivery-calendar.json` | 다른 확장/템플릿 레이아웃에 주입되는 조각 |
<!-- @generated:layout-extensions END -->

<!-- @intent START -->
`shop_checkout_extensions` 지점에 꽃배달관 안내 조각을 끼웁니다. 플러그인이 완전한 페이지를 등록할 수 없으므로 방문자 화면 개입은 이 조각이 유일합니다. 조각에서 쓰는 컴포넌트는 템플릿 제공분만 씁니다.
<!-- @intent END -->

## 미들웨어

<!-- @generated:middleware START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
_등록하는 미들웨어가 없습니다._
<!-- @generated:middleware END -->

<!-- @intent START -->
미들웨어를 두지 않습니다. 슬롯 조회·예약은 라우트 미들웨어(`auth:sanctum`·`permission`)로 충분합니다.
<!-- @intent END -->

## 브로드캐스트 채널

<!-- @generated:channels START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
_등록하는 브로드캐스트 채널이 없습니다._
<!-- @generated:channels END -->

<!-- @intent START -->
실시간 반영이 필요한 화면이 없어 채널을 두지 않습니다. 슬롯 잔여는 조회 시점 값이며 예약 후 `refetchDataSource` 로 갱신합니다.
<!-- @intent END -->

## 스케줄

<!-- @generated:schedules START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
_등록하는 스케줄이 없습니다._
<!-- @generated:schedules END -->

<!-- @intent START -->
스케줄을 두지 않습니다. 슬롯 마감 같은 시간 기반 처리는 요청 시점에 판정합니다.
<!-- @intent END -->

## 알림 정의

<!-- @generated:notifications START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
_등록하는 알림 정의가 없습니다._
<!-- @generated:notifications END -->

<!-- @intent START -->
알림 정의를 두지 않습니다. 주문 상태 알림은 코어·이커머스 알림 체계를 씁니다.
<!-- @intent END -->
