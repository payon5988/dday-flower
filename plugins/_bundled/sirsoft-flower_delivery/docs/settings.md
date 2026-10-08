# 꽃배달 확장 (Flower Delivery) — 설정·권한·라우트

> 설정 스키마·권한·메뉴·라우트·의존 관계 · 진입점: [AGENTS.md](../AGENTS.md)

## 설정 스키마

<!-- @generated:settings-schema START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 키 | 타입 | 기본값 | 설명 |
|---|---|---|---|
| `same_day_cutoff` | `string` | `14:00` | 당일배송 마감 시각 |
| `slot_cache_ttl` | `integer` | `300` | 슬롯 조회 캐시 TTL(초) |

기본값 파일: `config/settings/defaults.json` · 설정 화면 레이아웃: `resources/layouts/admin/plugin_settings.json`
<!-- @generated:settings-schema END -->

<!-- @intent START -->
`same_day_cutoff` 는 당일 슬롯 마감 판정의 입력이며 실제 판정은 `DeliverySlotService` 가 소유합니다. `slot_cache_ttl` 은 슬롯 조회 캐시 유지 시간으로, 주문 시점의 `lockForUpdate` 와 무관합니다 — 캐시가 오래되어도 초과 예약은 DB 락이 막습니다.
<!-- @intent END -->

## 권한

<!-- @generated:permissions START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 카테고리 | 이름 | 액션 | 라우트 키 |
|---|---|---|---|
| `slots` | 배송 슬롯 | `view`, `update` | - |
<!-- @generated:permissions END -->

<!-- @intent START -->
슬롯 토글(`admin/delivery-slots/{id}/toggle`)은 `sirsoft-flower_delivery.slots.update` 권한을 요구합니다. 공개 슬롯 조회와 사용자 예약·구독 API 는 권한 없이 Sanctum 인증(예약·구독) 또는 무인증(조회)으로 동작합니다.
<!-- @intent END -->

## 메뉴

<!-- @generated:menus START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 구분 | slug | 이름 | URL | 하위 |
|---|---|---|---|---|
| 관리자 | `sirsoft-flower_delivery-slot-board` | 꽃배달 슬롯 현황 | `/admin/plugins/sirsoft-flower_delivery/slot-board` | - |
| 관리자 | `sirsoft-flower_delivery-faq-board` | 꽃배달 FAQ 관리 | `/admin/plugins/sirsoft-flower_delivery/faq-board` | - |
<!-- @generated:menus END -->

<!-- @intent START -->
관리자 메뉴를 두지 않습니다. 슬롯 관리는 코어 「플러그인 관리 → 설정」 자동 경로(`plugin_settings.json`)와 API 로 수행합니다. 메뉴가 필요해지면 `Plugin::getAdminMenus()` 선언과 함께 이 절을 갱신합니다.
<!-- @intent END -->

## 라우트

<!-- @generated:routes START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 종류 | 파일 | URL prefix |
|---|---|---|
| `api` | `src/routes/api.php` | `/api/plugins/sirsoft-flower_delivery/...` |

확장 라우트는 **활성 상태인 확장의 것만** 등록됩니다. 라우트 정의를 바꾸면 라우트 캐시 재생성이 필요합니다.
<!-- @generated:routes END -->

<!-- @intent START -->
4개 엔드포인트(공개 조회 1 · 사용자 2 · 관리자 1)이며 전부에 `name()` 을 답니다. 라우트 추가·변경 후에는 `plugin:update` 흐름의 라우트 캐시 재생성을 거칩니다. 상세 파라미터·응답은 [API 레퍼런스](api/README.md)를 봅니다.
<!-- @intent END -->

## 의존 관계

<!-- @generated:dependencies START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
**이 확장이 의존하는 확장**

| 확장 | 유형 | 버전 제약 | 번들 |
|---|---|---|---|
| `sirsoft-ecommerce` | 모듈 | `>=1.2.1` | ✅ |

**이 확장에 의존하는 확장** (이 확장을 비활성화하면 함께 영향을 받습니다)

없음.
<!-- @generated:dependencies END -->

<!-- @intent START -->
이커머스가 없으면 주문 후킹(`FlowerOrderAfterCreateListener`)이 발화하지 않을 뿐 슬롯 API 는 독립 동작합니다. 그럼에도 manifest 에 `sirsoft-ecommerce >=1.2.1` 을 선언한 것은 이 플러그인의 존재 이유(꽃배달 특화 상점)가 그 모듈 없이는 성립하지 않기 때문입니다.
<!-- @intent END -->
