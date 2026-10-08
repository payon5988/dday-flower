# 그누보드7 꽃배달 확장 플러그인 — 에이전트 가이드

> 이 문서는 이 플러그인을 수정하는 에이전트·확장개발자를 위한 것입니다. 도입 검토·운영 관점은 [README.md](README.md) 를 보세요.

## TL;DR (5초 요약)

```text
1. 유형: 플러그인 (sirsoft-flower_delivery) — 배송 슬롯·메시지 카드·정기구독·맞춤옵션·예약 소유 (테이블 5개)
2. 확장 방식: sirsoft-ecommerce.order.after_create 구독 + 자체 슬롯 예약 API 4종. 이커머스 코드는 이 플러그인을 모른다
3. 건드리면 안 되는 것: 코어·이커머스 직접 수정, 메시지 평문 저장, bookSlot 의 lockForUpdate 생략, Filter 없는 훅 반환
4. 작업 위치: plugins/_bundled/sirsoft-flower_delivery — 활성 디렉토리 직접 수정 금지
5. 반영: php artisan plugin:update sirsoft-flower_delivery --force
```

## 1. 이 확장은 무엇인가

<!-- @intent START -->
`sirsoft-ecommerce` 를 베이스로 꽃배달 특화 기능(배송 슬롯 매트릭스·암호화 메시지 카드·정기구독 일시정지)을 얹는 플러그인입니다. 상품·주문·결제의 원본 상태는 이커머스가 계속 소유하고, 이 플러그인은 꽃배달에 필요한 **추가 상태 3종**만 소유합니다 — `g7_flower_delivery_slots`(날짜·시간대별 정원/예약), `g7_flower_message_cards`(주문당 1 메시지, 암호화 저장), `g7_flower_subscriptions`(주기·다음배송일·상태).

**설계 원칙**: 이커머스 상품·주문은 ID 값으로만 참조하고 FK 를 걸지 않습니다 — 모듈이 없는 환경에서도 플러그인 마이그레이션이 깨지지 않게 하기 위함입니다. 금액·결제·PG 는 손대지 않습니다 (그 영역의 SSoT 는 이커머스 `OrderCalculationService` 와 PG 플러그인입니다).

**의도적으로 하지 않는 것**: 방문자 화면 소유(템플릿 영역 — `/flower-premium` 은 `sirsoft-basic` 이 그립니다), 실시간 브로드캐스트(슬롯 잔여는 조회 시점 값 + 예약 후 refetch), 관리자 메뉴(설정 화면 1개로 충분), 편집기 스펙(기여 화면이 작아 코어 팔레트로 충분).
<!-- @intent END -->

## 2. 디렉토리 지도

<!-- @generated:directory-map START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 경로 | 역할 | 수정 시 필요한 절차 |
|---|---|---|
| `plugin.json` | manifest (버전 SSoT) | version 변경 시 package.json·package-lock.json·composer.json 동기화 |
| `plugin.php` | 진입 클래스 (선언형 표면 SSoT) | 표면 변경 시 `ext:docgen` 재실행 + 코어 최소 버전 검토 |
| `src/Http/Controllers/` | 컨트롤러 | API 표면 변경 시 `api:docgen` 재실행 |
| `src/Http/Requests/` | FormRequest (검증 SSoT) | 검증 규칙은 Service 가 아니라 여기에 둔다 |
| `src/Http/Resources/` | API 리소스 | 목록 응답은 화면이 실제로 그리는 것만 싣는다 |
| `src/Services/` | 비즈니스 로직 | Repository 인터페이스 주입 (구체 클래스 금지) |
| `src/Repositories/` | 데이터 접근 | 목록 쿼리는 컬럼 프루닝·정렬 화이트리스트 확인 |
| `src/Models/` | Eloquent 모델 | 스키마 변경 시 마이그레이션 + 업그레이드 스텝 동반 |
| `src/Listeners/` | 훅 리스너 | Repository 경유 (Model·DB 파사드 직접 접근 금지) |
| `src/Enums/` | 상태·타입·분류 | 문자열 리터럴 대신 Enum 을 SSoT 로 둔다 |
| `src/routes/` | 라우트 | 모든 라우트에 `name()` 필수 |
| `database/migrations/` | 마이그레이션 | 한국어 comment + `down()` 필수, 기설치본은 업그레이드 스텝으로 백필 |
| `resources/layouts/` | 레이아웃 JSON | `php artisan plugin:update sirsoft-flower_delivery --force` (빌드 불필요) |
| `resources/routes.json` | 라우트 → 레이아웃 매핑 | `php artisan plugin:update sirsoft-flower_delivery --force` |
| `resources/js/` | 프론트 엔트리·핸들러 | `php artisan plugin:build` → `php artisan plugin:update sirsoft-flower_delivery --force` |
| `resources/extensions/` | 다른 확장 레이아웃에 주입하는 조각 | `php artisan plugin:update sirsoft-flower_delivery --force` |
| `dist/` | 커밋되는 빌드 산출물 | `--production` 으로 재빌드 (sourceMappingURL 잔존 금지) |
| `config/` | 확장 config | 설정 기본값은 settings 스키마와 어긋나지 않게 |
| `tests/` | 테스트 | 변경 범위만 필터 실행 |
| `CHANGELOG.md` | 변경 이력 | 버전 상향 시 항목 추가 (미기재 시 버전 상향 불가) |
| `components.json` | 편집기 컴포넌트 선언 (레이아웃 저작자가 읽는 props 계약) | `php artisan plugin:update sirsoft-flower_delivery --force` |
| `docs/` | 개발자 문서 | 표면 변경 시 `php artisan ext:docgen` 재실행 |
| `lang/` | 다국어 | 키 추가 시 ko·en 동시 반영 + 번들 ja 팩 동기화 |
<!-- @generated:directory-map END -->

## 3. 핵심 흐름

<!-- @intent START -->
**슬롯 예약**: `DeliverySlotController::reserve` → `ReserveDeliverySlotRequest`(검증 SSoT) → `DeliverySlotService::bookSlot()` 이 트랜잭션 + `lockForUpdate()` 로 행을 잠그고 정원 초과 시 `DeliverySlotFullException` → 컨트롤러가 422 로 매핑합니다. 목록 조회는 Repository 의 컬럼 프루닝 쿼리(`getByProductAndDate`)를 씁니다.

**주문 후 메시지 카드**: `sirsoft-ecommerce.order.after_create` 가 발화하면 `FlowerOrderAfterCreateListener::handleAfterCreate()` 가 주문 메모의 메시지 카드를 `MessageCardService` 로 암호화 저장합니다. 이커머스 코드는 한 줄도 고치지 않습니다.

**구독 일시정지**: `SubscriptionController::pause` → `SubscriptionService::pause($id, $userId)` 가 소유권을 확인하고 상태를 `paused` 로 바꿉니다. 남의 구독은 404 로 동일하게 응답합니다.
<!-- @intent END -->

## 4. 확장점

<!-- @generated:extension-points-summary START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 확장점 | 수 | 상세 |
|---|---|---|
| 발행 훅 | 3개 | [발행 훅](docs/extension-points.md#발행-훅) |
| 구독 훅 | 1개 | [구독 훅](docs/extension-points.md#구독-훅) |
| 훅 리스너 | 1개 | [훅 리스너](docs/extension-points.md#훅-리스너) |
| 레이아웃 확장 | 1개 | [레이아웃 확장](docs/extension-points.md#레이아웃-확장) |
| 미들웨어 | 0개 | [미들웨어](docs/extension-points.md#미들웨어) |
| 브로드캐스트 채널 | 0개 | [브로드캐스트 채널](docs/extension-points.md#브로드캐스트-채널) |
| 스케줄 | 0개 | [스케줄](docs/extension-points.md#스케줄) |
| 알림 정의 | 0개 | [알림 정의](docs/extension-points.md#알림-정의) |
<!-- @generated:extension-points-summary END -->

<!-- @intent START -->
외부 확장이 잡을 수 있는 것은 `sirsoft-flower_delivery.slot.reserved` 하나뿐이며, 아직 발행 지점이 없어 선언만 있습니다 (예약 확정 후 반응이 필요해지면 `bookSlot()` 커밋 직후에 발행을 추가합니다). 반대로 이 플러그인이 잡는 것은 `sirsoft-ecommerce.order.after_create` 하나입니다 — 이커머스 업그레이드 시 훅 이름 변경 여부를 확인합니다.
<!-- @intent END -->

## 5. 수정 시 동반 의무

- [ ] `_bundled` 에서만 수정하고 `php artisan plugin:update sirsoft-flower_delivery --force` 로 반영
- [ ] manifest version 상향 시 `package.json` · `composer.json` 동기화 + CHANGELOG 기재
- [ ] 스키마 변경 시 마이그레이션(한국어 comment + `down()`) + 기설치본 백필용 업그레이드 스텝
- [ ] 발행 훅 추가·이름 변경 시 `php artisan ext:docgen` 재실행 (구독하는 확장의 계약이 바뀝니다)
- [ ] API 표면 변경 시 `php artisan api:docgen --scope=plugin:sirsoft-flower_delivery` 재실행 + `docs/api/**` 갱신
- [ ] 레이아웃 JSON 변경 시 빌드 없이 update 만 — 신규 Tailwind 클래스는 빌드된 CSS 에 존재하는지 확인
- [ ] TSX/TS 변경 시 `--production` 재빌드 후 `dist/` 커밋 (sourceMappingURL 잔존 금지)
- [ ] 다국어 키 추가 시 ko·en 동시 반영 + 번들 ja 언어팩 증분 동기화
- [ ] 플러그인은 완전한 페이지 레이아웃을 등록할 수 없다 — 설정 화면과 `layout_extensions` 만
- [ ] Filter 훅을 구독한다면 `'type' => 'filter'` 를 선언했는지 확인 — 누락 시 반환값이 조용히 버려진다
- [ ] 금전이 움직이는 훅을 구독한다면 `'sync' => true` — 본 플러그인은 금전 훅 미구독
- [ ] `bookSlot()` 의 `lockForUpdate()` 를 우회하거나 생략하지 않는다 — 이중 예약이 생긴다
- [ ] 메시지 본문을 평문 컬럼에 저장하지 않는다 — `encrypted` 캐스트가 PIPA 대응이다

## 6. 금지 패턴

<!-- @intent START -->
| 금지 | 올바른 사용 | 이유 |
|---|---|---|
| `bookSlot` 에서 잠금 없이 `current_bookings` 를 읽고 증가 | 트랜잭션 + `lockForUpdate()` 후 increment | 동시 예약에서 정원을 초과한다 — 캐시·사전 조회는 방어가 아니다 |
| 메시지 본문을 평문으로 저장 | `encrypted_message` + `encrypted` 캐스트 | 수령인 이름·문구는 개인정보라 DB 암호화 저장한다 (PIPA) |
| 이커머스 상품·주문에 FK 제약 | ID 값 참조 (FK 미부착) | 모듈이 없는 환경에서 플러그인 마이그레이션이 깨진다 |
| 리스너에서 `Model::query()` · `DB::table()` 직접 호출 | Repository 인터페이스 주입 | 리스너가 데이터 접근 규약의 예외가 되면 그 예외가 번진다 |
| 플러그인에 완전한 페이지 레이아웃 등록 | 설정 화면과 `layout_extensions` 만 | 페이지 소유권은 모듈·템플릿에 있다 — 경로를 다투면 설치 순서에 따라 화면이 바뀐다 |
| 금전 훅을 기본 설정(큐)으로 구독 | `'sync' => true` | 커밋 뒤 실행이라 예외를 던져도 롤백되지 않는다 (현재 금전 훅 미구독이나 추가 시 적용) |
<!-- @intent END -->

## 7. 테스트 실행

<!-- @generated:test-commands START — ext:docgen 이 갱신. 이 블록 안은 직접 수정하지 않는다 -->
| 종류 | 개수 | 위치 |
|---|---|---|
| PHPUnit | 1개 | `plugins/_bundled/sirsoft-flower_delivery/tests` |
| Vitest | 0개 | — |
| Playwright | 0개 | — |
| 시나리오 매니페스트 | 0개 | — |

기저 TestCase: `tests/PluginTestCase.php` — 확장 테스트는 이 클래스를 상속합니다 (`Tests\TestCase` 직접 상속 금지).

```bash
# PHPUnit (변경 범위만) (Bash)
php vendor/bin/phpunit plugins/_bundled/sirsoft-flower_delivery/tests --filter='<대상클래스>'

```

무필터 전체 실행은 금지되어 있습니다 — 변경 범위에 걸리는 대상만 지정해 실행합니다.
<!-- @generated:test-commands END -->

기저 TestCase: `tests/PluginTestCase.php` — 확장 테스트는 이 클래스를 상속합니다 (`Tests\TestCase` 직접 상속 금지).

```bash
# PHPUnit (변경 범위만) (Bash)
php vendor/bin/phpunit plugins/_bundled/sirsoft-flower_delivery/tests --filter='<대상클래스>'
```

무필터 전체 실행은 금지되어 있습니다 — 변경 범위에 걸리는 대상만 지정해 실행합니다.

## 8. 문서 목차

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
