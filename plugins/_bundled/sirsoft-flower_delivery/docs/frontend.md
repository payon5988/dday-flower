# 꽃배달 확장 (Flower Delivery) — 프론트엔드

> 레이아웃·액션 핸들러·전역 진입점·에셋 · 진입점: [AGENTS.md](../AGENTS.md)

## 레이아웃

<!-- @generated:layouts START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
레이아웃 3개 (루트: `resources/layouts`).

| 그룹 | 개수 |
|---|---|
| `admin` | 3개 |

| 레이아웃 | 그룹 | 종류 | extends |
|---|---|---|---|
| `faq_board` | `admin` | 화면 | `_admin_base` |
| `plugin_settings` | `admin` | 화면 | - |
| `slot_board` | `admin` | 화면 | `_admin_base` |
<!-- @generated:layouts END -->

<!-- @intent START -->
`plugin_settings.json` 하나만 둡니다. 플러그인은 완전한 페이지 레이아웃을 등록할 수 없으므로 방문자 화면(`/flower-premium`)은 템플릿(`sirsoft-basic` `shop/flower_premium`)이 소유하고, 이 플러그인은 `resources/extensions/checkout-delivery-calendar.json` 조각으로 체크아웃 화면에만 개입합니다.
<!-- @intent END -->

## 액션 핸들러

<!-- @generated:handlers START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
_등록하는 액션 핸들러가 없습니다._
<!-- @generated:handlers END -->

<!-- @intent START -->
ActionDispatcher 핸들러를 등록하지 않습니다. 슬롯 예약·구독 변경은 레이아웃의 내장 `apiCall` 핸들러로 충분하며, 플러그인 번들은 `FlowerDeliveryCalendar` 표시 컴포넌트와 테마 상수·전화번호 마스킹 헬퍼만 노출합니다.
<!-- @intent END -->

## 전역 진입점

<!-- @generated:frontend-entry START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
_프론트 엔트리포인트가 없습니다._
<!-- @generated:frontend-entry END -->

<!-- @intent START -->
진입점은 `resources/js/index.ts` 로, `FlowerDeliveryCalendar`·`FLOWER_THEME`·`formatMaskedPhone` 을 export 합니다. 전역 재등록 진입점(`window.__X.initPlugin`)을 두지 않은 것은 핸들러를 등록하지 않으므로 로케일 전환 후 재등록할 것이 없기 때문입니다.
<!-- @intent END -->

## 에셋

<!-- @generated:assets START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 경로 | 구분 |
|---|---|
| `dist/css/plugin.css` | 빌드 산출물 (커밋 대상) |
| `dist/js/plugin.iife.js` | 빌드 산출물 (커밋 대상) |

로딩 설정: `{"strategy":"global","priority":100,"dependencies":[]}`
<!-- @generated:assets END -->

<!-- @intent START -->
`global` 로딩·우선순위 100 으로 모든 페이지에 실립니다. CSS 는 Tailwind 없이 동작하는 독립 변수(`--flower-*`)로 두어 템플릿 빌드와 무관하게 색이 깨지지 않습니다. 템플릿 쪽 브랜드 유틸리티(`bg-forest` 등)는 `sirsoft-basic` 의 `main.css` @theme + safelist 가 소유합니다.
<!-- @intent END -->
