# 그누보드7 꽃배달 확장 플러그인

**그누보드7 플러그인 · sirsoft-flower_delivery**
sirsoft-ecommerce 기반 꽃배달 특화 기능 (배송 슬롯 · 메시지 카드 · 정기구독)

<!-- @generated:badges START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
<p align="center">
  <img src="https://img.shields.io/badge/version-0.8.0-0066FF?style=flat-square" alt="version 0.8.0">
  <img src="https://img.shields.io/badge/type-%ED%94%8C%EB%9F%AC%EA%B7%B8%EC%9D%B8-555555?style=flat-square" alt="type 플러그인">
  <img src="https://img.shields.io/badge/%EA%B7%B8%EB%88%84%EB%B3%B4%EB%93%9C7-%3E%3D7.0.10-1F883D?style=flat-square" alt="그누보드7 &gt;=7.0.10">
  <img src="https://img.shields.io/badge/license-MIT-8250DF?style=flat-square" alt="license MIT">
  <img src="https://img.shields.io/badge/requires-sirsoft--ecommerce-BF8700?style=flat-square" alt="requires sirsoft-ecommerce">
</p>
<!-- @generated:badges END -->

---

[소개](#소개) · [주요 기능](#주요-기능) · [동작 방식](#동작-방식) · [요구 사항](#요구-사항) · [설치](#설치) · [관리자 설정](#관리자-설정) · [사용 방법](#사용-방법) · [다른 확장과의 연동](#다른-확장과의-연동) · [문서](#문서) · [트러블슈팅](#트러블슈팅) · [변경 이력](#변경-이력) · [라이선스](#라이선스)

---

## 소개

<!-- @intent START -->
프리미엄 꽃배달 쇼핑몰에 필요한 기능을 `sirsoft-ecommerce` 위에 얹는 플러그인입니다.
상품별 "배송 가능 일자·시간대" 매트릭스, 주문당 1매의 암호화 메시지 카드, 정기구독 일시정지를 제공합니다.

상품·주문·결제의 원본은 이커머스가 계속 소유하고, 이 플러그인은 꽃배달 추가 상태 3종만 소유합니다.
코어·이커머스 파일은 한 줄도 고치지 않으며, 주문 흐름 개입은 훅 구독 하나로만 합니다.
<!-- @intent END -->

## 주요 기능

<!-- @intent START -->
| 영역 | 설명 |
|---|---|
| 배송 슬롯 조회 | 상품·날짜별 시간대 목록 (시간·예약가능·잔여) — 미지정 시 내일 날짜 기본 |
| 배송 슬롯 예약 | 트랜잭션 + 행 잠금으로 이중 예약 차단, 마감 시 422 |
| 메시지 카드 | 주문당 1매, AES 암호화 저장 (PIPA) |
| 정기구독 | 주간/월간 구독의 배송 일시정지 (소유권 확인) |
| 체크아웃 조각 | 체크아웃 화면에 꽃배달관 안내 주입 |
| 방문자 화면 | 프리미엄 꽃배달관(`/flower-premium`)은 템플릿이 소유 — 이 플러그인은 API 제공 |
<!-- @intent END -->

## 동작 방식

<!-- @intent START -->
```mermaid
flowchart LR
  A[슬롯 조회] --> B["슬롯 예약 (잠금)"]
  B --> C[주문 생성 (이커머스)]
  C --> D["after_create 훅 → 메시지 카드 저장"]
  E[구독 일시정지] --> F["다음 배송 보류"]
```

예약은 요청 시점에 행을 잠그고 정원을 확인하므로, 조회 캐시가 오래되어도 초과 예약은 일어나지 않습니다.
메시지 카드는 주문 생성 후에만 저장됩니다 — 예약 단계에서는 저장하지 않습니다.
<!-- @intent END -->

## 요구 사항

<!-- @generated:requirements START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 항목 | 값 |
|---|---|
| 그누보드7 코어 | `>=7.0.10` |
| PHP | `^8.2` |
| 의존 모듈 | `sirsoft-ecommerce` `>=1.2.1` |
<!-- @generated:requirements END -->

## 설치

<!-- @generated:install START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
```bash
# 번들 설치 (코어에 동봉된 소스에서 설치)
php artisan plugin:install sirsoft-flower_delivery

# 활성화
php artisan plugin:activate sirsoft-flower_delivery

# 업데이트 (번들 소스 기준 강제 반영)
php artisan plugin:update sirsoft-flower_delivery --force
```
<!-- @generated:install END -->

```bash
php artisan plugin:install sirsoft-flower_delivery
php artisan plugin:activate sirsoft-flower_delivery
```

## 관리자 설정

<!-- @intent START -->
코어 「플러그인 관리 → 설정」에서 당일배송 마감 시각(`same_day_cutoff`, 기본 `14:00`)과
슬롯 조회 캐시 TTL(`slot_cache_ttl`, 기본 300초)을 바꿀 수 있습니다.
배송 슬롯의 활성·마감 전환은 관리자 API(`PATCH /admin/delivery-slots/{id}/toggle`)로 합니다.
<!-- @intent END -->

<!-- @generated:settings-summary START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 키 | 의미 | 기본값 |
|---|---|---|
| `same_day_cutoff` | 당일배송 마감 시각 | `14:00` |
| `slot_cache_ttl` | 슬롯 조회 캐시 TTL(초) | `300` |

개발자용 상세(타입·검증·저장 위치)는 [설정 스키마](docs/settings.md#설정-스키마) 를 보세요.
<!-- @generated:settings-summary END -->

## 사용 방법

<!-- @intent START -->
1. 상품별 배송 슬롯을 등록합니다 (현재는 API·DB 직접 등록 — 관리자 화면은 설정 화면만 제공합니다).
2. 방문자는 프리미엄 꽃배달관(`/flower-premium`)에서 날짜·시간을 예약합니다 (로그인 필요).
3. 주문이 생성되면 함께 전달된 메시지 카드가 암호화 저장됩니다.
4. 정기구독자는 구독 일시정지로 다음 배송을 보류할 수 있습니다.
<!-- @intent END -->

## 다른 확장과의 연동

<!-- @intent START -->
`sirsoft-ecommerce` 의 `order.after_create` 훅을 구독합니다 — 모듈 코드는 모릅니다.
체크아웃 화면(`sirsoft-basic` 의 `shop_checkout_extensions` 지점)에 안내 조각을 주입합니다.
`sirsoft-flower_delivery.slot.reserved` 훅을 발행 선언했으나 아직 발행 지점이 없으므로 구독해도 호출되지 않습니다.
<!-- @intent END -->

<!-- @generated:integrations START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
**이 확장이 의존하는 확장**

| 확장 | 유형 | 버전 제약 | 번들 |
|---|---|---|---|
| `sirsoft-ecommerce` | 모듈 | `>=1.2.1` | ✅ |

**이 확장에 의존하는 확장** (이 확장을 비활성화하면 함께 영향을 받습니다)

없음.
<!-- @generated:integrations END -->

## 문서

<!-- @intent START -->
개발자 문서는 [docs/](docs/README.md) 에 있습니다 (아키텍처·확장점·데이터 모델·설정·프론트엔드·편집기 스펙·API 레퍼런스).
<!-- @intent END -->

<!-- @generated:docs-index START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 문서 | 내용 | 상태 |
|---|---|---|
| [docs/README.md](docs/README.md) | 문서 통합 목차와 실측 집계 | ✅ |
| [docs/architecture.md](docs/architecture.md) | 설계 의도·계층 지도·디렉토리 맵 | ✅ |
| [docs/extension-points.md](docs/extension-points.md) | 발행/구독 훅·미들웨어·채널·스케줄 | ✅ |
| [docs/data-model.md](docs/data-model.md) | 모델·소유 테이블·마이그레이션·Enum | ✅ |
| [docs/settings.md](docs/settings.md) | 설정 스키마·권한·메뉴·라우트·의존 관계 | ✅ |
| [docs/frontend.md](docs/frontend.md) | 레이아웃·액션 핸들러·전역 진입점·에셋 | ✅ |
| [docs/editor-spec.md](docs/editor-spec.md) | 레이아웃 편집기에 선언한 팔레트·컨트롤·샘플 데이터 | ✅ |
| [docs/api/](docs/api/README.md) | API 레퍼런스 (엔드포인트별 파라미터·응답 필드) | ✅ |
| [CHANGELOG.md](CHANGELOG.md) | 변경 이력 | ✅ |
<!-- @generated:docs-index END -->

## 트러블슈팅

<!-- @intent START -->
| 증상 | 원인·대처 |
|---|---|
| 슬롯이 있는데 조회가 비어 있다 | `date` 파라미터가 과거 날짜이면 422 입니다. 미지정 시 내일 날짜로 조회합니다 |
| 예약이 401 이다 | 슬롯 예약은 Sanctum 인증이 필요합니다. 로그인 후 다시 시도합니다 |
| 마감 슬롯 예약이 422 다 | 정상입니다 — 정원이 찼거나 비활성 슬롯입니다 |
| 체크아웃에 꽃배달 안내가 안 보인다 | 플러그인 비활성 시 조각도 함께 내려갑니다. 활성화 상태를 확인합니다 |
<!-- @intent END -->

## 변경 이력

<!-- @intent START -->
[CHANGELOG.md](CHANGELOG.md) 를 봅니다. 버전 상향 시 항목 추가가 필수입니다 (미기재 시 버전 상향 불가).
<!-- @intent END -->

## 라이선스

<!-- @intent START -->
MIT — [LICENSE](LICENSE) 가 있으면 그 파일을, 없으면 `plugin.json` 의 license 필드를 따릅니다.
<!-- @intent END -->
