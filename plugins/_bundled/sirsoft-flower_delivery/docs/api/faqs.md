# Faqs API 레퍼런스

> **소유**: plugin `sirsoft-flower_delivery` · **생성**: `php artisan api:docgen` (실측 기반). @generated 블록은 재생성 시 갱신되며, 사람이 작성한 설명은 보존됩니다.

---

## TL;DR (5초 요약)

```text
1. 이 문서는 실제 API 호출로 실측한 Faqs 엔드포인트 레퍼런스입니다
2. 각 엔드포인트: 메서드/URI/권한 + 요청 파라미터 표 + 요청 예시(raw HTTP) + 실측 응답 필드 표 + 응답 예시(envelope)
3. 응답 필드의 예시값·응답 예시 JSON 은 실제 호출 응답에서 관측된 값입니다
4. 갱신: 코드 변경 후 php artisan api:docgen 재실행
5. 설명(TODO) 칸은 사람이 채웁니다
```

---


### GET /api/plugins/sirsoft-flower_delivery/admin/faqs
<!-- @generated:start:api.plugins.sirsoft-flower_delivery.admin.faqs.index -->
- **라우트명**: `api.plugins.sirsoft-flower_delivery.admin.faqs.index`
- **컨트롤러**: `Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin\FlowerFaqAdminController@index`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-flower_delivery.slots.view`

**요청 파라미터**

_요청 파라미터 없음._

**요청 예시**

```http
GET /api/plugins/sirsoft-flower_delivery/admin/faqs HTTP/1.1
Host: api.example.com
Accept: application/json
Authorization: Bearer {YOUR_TOKEN}
```

**응답 필드** (`data` 내부)

_단건 응답: `data` 객체의 필드._

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| faqs | array | `[{"id":1,"category":"order","question":{"en":"What is the…` | <!-- TODO: 설명 --> |

**응답 예시**

<!-- @probed -->

```http
HTTP/1.1 200
```

```json
{
    "success": true,
    "message": "자주 묻는 질문을 조회했습니다.",
    "data": {
        "faqs": [
            {
                "id": 1,
                "category": "order",
                "question": {
                    "en": "What is the same-day cutoff?",
                    "ja": "当日配送の締切は何時ですか？",
                    "ko": "당일배송 마감은 몇 시인가요?"
                },
                "answer": {
                    "en": "Orders by 2 PM leave the same day. Later orders move to the earliest slot tomorrow.",
                    "ja": "14時までのご注文は当日発送します。以降は翌日の最も早い時間帯を自動案内します。",
                    "ko": "14시 이전 주문까지 당일 출발합니다. 이후 주문은 다음날 가장 빠른 시간으로 자동 안내됩니다."
                },
                "sort_order": 1,
                "is_active": true,
                "tenant_id": null,
                "created_at": "2026-10-05T23:37:47.000000Z",
                "updated_at": "2026-10-05T23:37:47.000000Z"
            },
            {
                "id": 2,
                "category": "delivery",
                "question": {
                    "en": "Who can read my message card?",
                    "ja": "メッセージカードは誰が読めますか？",
                    "ko": "메시지 카드는 누가 볼 수 있나요?"
                },
                "answer": {
                    "en": "It is encrypted — no one but the delivery rider can read it. The content never shows on screen.",
                    "ja": "暗号化保存され、配達員以外は閲覧できません。内容が画面に表示されることはありません。",
                    "ko": "암호화 저장되어 배송 기사 외에는 누구도 열람할 수 없습니다. 주문 완료 화면에서도 내용은 표시되지 않습니다."
                },
                "sort_order": 2,
                "is_active": true,
                "tenant_id": null,
                "created_at": "2026-10-05T23:37:47.000000Z",
                "updated_at": "2026-10-05T23:37:47.000000Z"
            },
            {
                "id": 3,
                "category": "subscription",
                "question": {
                    "en": "Can I pause my subscription?",
                    "ja": "定期便を一時休止できますか？",
                    "ko": "정기구독을 잠시 쉴 수 있나요?"
                },
                "answer": {
                    "en": "Pause and resume anytime in the premium hall subscription section. Paused weeks are not billed.",
                    "ja": "プレミアム館の定期便セクションで一時停止・再開できます。停止期間の料金は請求されません。",
                    "ko": "프리미엄관 구독 섹션에서 일시정지·재개할 수 있습니다. 요금은 정지 기간만큼 청구되지 않습니다."
                },
                "sort_order": 3,
                "is_active": true,
                "tenant_id": null,
                "created_at": "2026-10-05T23:37:47.000000Z",
                "updated_at": "2026-10-05T23:37:47.000000Z"
            }
        ]
    }
}
```

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-flower_delivery.slots.view`)이 없는 경우 |

<!-- @generated:end -->

**설명** <!-- TODO: 이 엔드포인트의 용도·주의사항·예시 시나리오를 작성하세요 -->


### POST /api/plugins/sirsoft-flower_delivery/admin/faqs
<!-- @generated:start:api.plugins.sirsoft-flower_delivery.admin.faqs.store -->
- **라우트명**: `api.plugins.sirsoft-flower_delivery.admin.faqs.store`
- **컨트롤러**: `Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin\FlowerFaqAdminController@store`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-flower_delivery.slots.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| category | body | string | 아니오 | max 30 | <!-- TODO: 용도 --> |
| question | body | array | 예 | min 1 | <!-- TODO: 용도 --> |
| answer | body | array | 예 | min 1 | <!-- TODO: 용도 --> |
| sort_order | body | integer | 아니오 | min 0, max 10000 | 표시 정렬 순서 값 (작을수록 우선) |
| is_active | body | boolean | 아니오 | — | 활성 여부 (true 활성 / false 비활성) |

**요청 예시**

```http
POST /api/plugins/sirsoft-flower_delivery/admin/faqs HTTP/1.1
Host: api.example.com
Accept: application/json
Authorization: Bearer {YOUR_TOKEN}
Content-Type: application/json

{
    "category": "예시값",
    "question": [
        "예시값"
    ],
    "answer": [
        "예시값"
    ],
    "sort_order": 1,
    "is_active": true
}
```

**응답 필드** (`data` 내부)

<!-- 실측 제외: http-422 — 응답 필드는 사람이 작성하세요. -->

**응답 예시**

<!-- 실측 제외: http-422 — 응답 예시는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-flower_delivery.slots.update`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** <!-- TODO: 이 엔드포인트의 용도·주의사항·예시 시나리오를 작성하세요 -->


### DELETE /api/plugins/sirsoft-flower_delivery/admin/faqs/{id}
<!-- @generated:start:api.plugins.sirsoft-flower_delivery.admin.faqs.destroy -->
- **라우트명**: `api.plugins.sirsoft-flower_delivery.admin.faqs.destroy`
- **컨트롤러**: `Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin\FlowerFaqAdminController@destroy`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-flower_delivery.slots.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| id | path | string | 예 | — | 대상 리소스의 식별자 |

**요청 예시**

```http
DELETE /api/plugins/sirsoft-flower_delivery/admin/faqs/{id} HTTP/1.1
Host: api.example.com
Accept: application/json
Authorization: Bearer {YOUR_TOKEN}
```

**응답 필드** (`data` 내부)

<!-- 실측 제외: unresolved-path-param — 응답 필드는 사람이 작성하세요. -->

**응답 예시**

<!-- 실측 제외: unresolved-path-param — 응답 예시는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-flower_delivery.slots.update`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** <!-- TODO: 이 엔드포인트의 용도·주의사항·예시 시나리오를 작성하세요 -->


### PUT /api/plugins/sirsoft-flower_delivery/admin/faqs/{id}
<!-- @generated:start:api.plugins.sirsoft-flower_delivery.admin.faqs.update -->
- **라우트명**: `api.plugins.sirsoft-flower_delivery.admin.faqs.update`
- **컨트롤러**: `Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin\FlowerFaqAdminController@update`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-flower_delivery.slots.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| id | path | string | 예 | — | 대상 리소스의 식별자 |
| category | body | string | 아니오 | max 30 | <!-- TODO: 용도 --> |
| question | body | array | 예 | min 1 | <!-- TODO: 용도 --> |
| answer | body | array | 예 | min 1 | <!-- TODO: 용도 --> |
| sort_order | body | integer | 아니오 | min 0, max 10000 | 표시 정렬 순서 값 (작을수록 우선) |
| is_active | body | boolean | 아니오 | — | 활성 여부 (true 활성 / false 비활성) |

**요청 예시**

```http
PUT /api/plugins/sirsoft-flower_delivery/admin/faqs/{id} HTTP/1.1
Host: api.example.com
Accept: application/json
Authorization: Bearer {YOUR_TOKEN}
Content-Type: application/json

{
    "category": "예시값",
    "question": [
        "예시값"
    ],
    "answer": [
        "예시값"
    ],
    "sort_order": 1,
    "is_active": true
}
```

**응답 필드** (`data` 내부)

<!-- 실측 제외: unresolved-path-param — 응답 필드는 사람이 작성하세요. -->

**응답 예시**

<!-- 실측 제외: unresolved-path-param — 응답 예시는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-flower_delivery.slots.update`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** <!-- TODO: 이 엔드포인트의 용도·주의사항·예시 시나리오를 작성하세요 -->


### PATCH /api/plugins/sirsoft-flower_delivery/admin/faqs/{id}/toggle
<!-- @generated:start:api.plugins.sirsoft-flower_delivery.admin.faqs.toggle -->
- **라우트명**: `api.plugins.sirsoft-flower_delivery.admin.faqs.toggle`
- **컨트롤러**: `Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin\FlowerFaqAdminController@toggle`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-flower_delivery.slots.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| id | path | string | 예 | — | 대상 리소스의 식별자 |

**요청 예시**

```http
PATCH /api/plugins/sirsoft-flower_delivery/admin/faqs/{id}/toggle HTTP/1.1
Host: api.example.com
Accept: application/json
Authorization: Bearer {YOUR_TOKEN}
```

**응답 필드** (`data` 내부)

<!-- 실측 제외: unresolved-path-param — 응답 필드는 사람이 작성하세요. -->

**응답 예시**

<!-- 실측 제외: unresolved-path-param — 응답 예시는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-flower_delivery.slots.update`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** <!-- TODO: 이 엔드포인트의 용도·주의사항·예시 시나리오를 작성하세요 -->


### GET /api/plugins/sirsoft-flower_delivery/faqs
<!-- @generated:start:api.plugins.sirsoft-flower_delivery.faqs.index -->
- **라우트명**: `api.plugins.sirsoft-flower_delivery.faqs.index`
- **컨트롤러**: `Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Public\FaqController@index`
- **인증/권한**: 공개 (인증 불필요)

**요청 파라미터**

_요청 파라미터 없음._

**요청 예시**

```http
GET /api/plugins/sirsoft-flower_delivery/faqs HTTP/1.1
Host: api.example.com
Accept: application/json
```

**응답 필드** (`data` 내부)

_단건 응답: `data` 객체의 필드._

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| faqs | array | `[{"id":1,"category":"order","question":"당일배송 마감은 몇 시인가요?"…` | <!-- TODO: 설명 --> |

**응답 예시**

<!-- @probed -->

```http
HTTP/1.1 200
```

```json
{
    "success": true,
    "message": "자주 묻는 질문을 조회했습니다.",
    "data": {
        "faqs": [
            {
                "id": 1,
                "category": "order",
                "question": "당일배송 마감은 몇 시인가요?",
                "answer": "14시 이전 주문까지 당일 출발합니다. 이후 주문은 다음날 가장 빠른 시간으로 자동 안내됩니다."
            },
            {
                "id": 2,
                "category": "delivery",
                "question": "메시지 카드는 누가 볼 수 있나요?",
                "answer": "암호화 저장되어 배송 기사 외에는 누구도 열람할 수 없습니다. 주문 완료 화면에서도 내용은 표시되지 않습니다."
            },
            {
                "id": 3,
                "category": "subscription",
                "question": "정기구독을 잠시 쉴 수 있나요?",
                "answer": "프리미엄관 구독 섹션에서 일시정지·재개할 수 있습니다. 요금은 정지 기간만큼 청구되지 않습니다."
            }
        ]
    }
}
```

**에러 응답**

_대표 에러 없음 (공개 조회). <!-- TODO: 도메인 특이 에러가 있으면 보강 -->_

<!-- @generated:end -->

**설명** <!-- TODO: 이 엔드포인트의 용도·주의사항·예시 시나리오를 작성하세요 -->

