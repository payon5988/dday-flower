# 꽃배달 확장 (Flower Delivery) — 문서 목차

<!-- @generated:stats START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
**훅 수**: 3 · **구독 훅 수**: 1 · **라우트 수**: 20 · **모델 수**: 6 · **테이블 수**: 6 · **마이그레이션 수**: 7 · **레이아웃 수**: 3 · **핸들러 수**: 0
<!-- @generated:stats END -->

## 문서 목차

<!-- @generated:doc-toc START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 문서 | 내용 |
|---|---|
| [architecture.md](architecture.md) | 설계 의도·계층 지도·디렉토리 맵 |
| [extension-points.md](extension-points.md) | 발행/구독 훅·미들웨어·채널·스케줄 |
| [data-model.md](data-model.md) | 모델·소유 테이블·마이그레이션·Enum |
| [settings.md](settings.md) | 설정 스키마·권한·메뉴·라우트·의존 관계 |
| [frontend.md](frontend.md) | 레이아웃·액션 핸들러·전역 진입점·에셋 |
| [editor-spec.md](editor-spec.md) | 레이아웃 편집기에 선언한 팔레트·컨트롤·샘플 데이터 |
| [api/](api/README.md) | API 레퍼런스 |
| [../AGENTS.md](../AGENTS.md) | 에이전트·확장개발자 진입점 |
| [../README.md](../README.md) | 사람(도입검토자·운영자) 진입점 |
<!-- @generated:doc-toc END -->

- 설계: 코어·이커머스 무수정, 플러그인 격리
- DB: `g7_flower_delivery_slots`, `g7_flower_message_cards`, `g7_flower_subscriptions` (신규만, 기존 테이블 미변경)
- API: `src/routes/api.php` (name() 필수 준수) — [API 레퍼런스](api/README.md)
- 훅: `sirsoft-ecommerce.order.after_create` 구독, `sirsoft-flower_delivery.slot.reserved` 발행 선언
- 프론트: `resources/js/FlowerDeliveryCalendar.tsx` + `resources/css/flower.css` (독립 변수 `--flower-*`)
- 전체 페이지(`/flower-premium`)는 템플릿 영역 — 플러그인은 `layout_extensions` 조각만 제공
