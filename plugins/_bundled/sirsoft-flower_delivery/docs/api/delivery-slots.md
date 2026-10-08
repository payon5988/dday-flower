# Delivery Slots API 레퍼런스

> **소유**: plugin `sirsoft-flower_delivery` · **생성**: `php artisan api:docgen` (실측 기반). @generated 블록은 재생성 시 갱신되며, 사람이 작성한 설명은 보존됩니다.

---

## TL;DR (5초 요약)

```text
1. 이 문서는 실제 API 호출로 실측한 Delivery Slots 엔드포인트 레퍼런스입니다
2. 각 엔드포인트: 메서드/URI/권한 + 요청 파라미터 표 + 요청 예시(raw HTTP) + 실측 응답 필드 표 + 응답 예시(envelope)
3. 응답 필드의 예시값·응답 예시 JSON 은 실제 호출 응답에서 관측된 값입니다
4. 갱신: 코드 변경 후 php artisan api:docgen 재실행
5. 설명(TODO) 칸은 사람이 채웁니다
```

---


### GET /api/plugins/sirsoft-flower_delivery/admin/delivery-slots/board
<!-- @generated:start:api.plugins.sirsoft-flower_delivery.admin.delivery-slots.board -->
- **라우트명**: `api.plugins.sirsoft-flower_delivery.admin.delivery-slots.board`
- **컨트롤러**: `Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin\DeliverySlotAdminController@board`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-flower_delivery.slots.view`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| date | query | date | 아니오 | — | <!-- TODO: 용도 --> |
| product_id | query | integer | 아니오 | min 1 | product 식별자 |

**요청 예시**

```http
GET /api/plugins/sirsoft-flower_delivery/admin/delivery-slots/board?date=2026-01-01&product_id=1 HTTP/1.1
Host: api.example.com
Accept: application/json
Authorization: Bearer {YOUR_TOKEN}
```

**응답 필드** (`data` 내부)

_단건 응답: `data` 객체의 필드._

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| date | string | `2026-10-03` | <!-- TODO: 설명 --> |
| day | object | `{"date":"2026-10-03","cutoff":"14:00","is_today":true,"sa…` | <!-- TODO: 설명 --> |
| slots | array | `[]` | 슬롯별 삽입 콘텐츠 맵 (베이스 레이아웃의 slot 위치에 주입) |

**응답 예시**

<!-- @probed -->

```http
HTTP/1.1 200
```

```json
{
    "success": true,
    "message": "슬롯 현황판을 조회했습니다.",
    "data": {
        "date": "2026-10-05",
        "day": {
            "date": "2026-10-05",
            "cutoff": "14:00",
            "is_today": true,
            "same_day_available": false,
            "same_day_remaining": 0
        },
        "slots": [
            {
                "id": 17,
                "time": "10:00-12:00",
                "max_capacity": 10,
                "current_bookings": 0,
                "remaining": 10,
                "is_active": true
            },
            {
                "id": 18,
                "time": "14:00-16:00",
                "max_capacity": 10,
                "current_bookings": 3,
                "remaining": 7,
                "is_active": true
            },
            {
                "id": 19,
                "time": "16:00-18:00",
                "max_capacity": 8,
                "current_bookings": 8,
                "remaining": 0,
                "is_active": true
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
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** <!-- TODO: 이 엔드포인트의 용도·주의사항·예시 시나리오를 작성하세요 -->


### PATCH /api/plugins/sirsoft-flower_delivery/admin/delivery-slots/{id}/toggle
<!-- @generated:start:api.plugins.sirsoft-flower_delivery.admin.delivery-slots.toggle -->
- **라우트명**: `api.plugins.sirsoft-flower_delivery.admin.delivery-slots.toggle`
- **컨트롤러**: `Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin\DeliverySlotAdminController@toggle`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-flower_delivery.slots.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| id | path | string | 예 | — | 대상 리소스의 식별자 |

**요청 예시**

```http
PATCH /api/plugins/sirsoft-flower_delivery/admin/delivery-slots/{id}/toggle HTTP/1.1
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


### POST /api/plugins/sirsoft-flower_delivery/delivery-slots/reserve
<!-- @generated:start:api.plugins.sirsoft-flower_delivery.delivery-slots.reserve -->
- **라우트명**: `api.plugins.sirsoft-flower_delivery.delivery-slots.reserve`
- **컨트롤러**: `Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Public\DeliverySlotController@reserve`
- **인증/권한**: `auth:sanctum`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| product_id | body | integer | 예 | min 1 | product 식별자 |
| delivery_date | body | date | 예 | — | delivery 날짜 |
| time_slot | body | string | 예 | max 30 | <!-- TODO: 용도 --> |
| message_card | body | array | 아니오 | — | <!-- TODO: 용도 --> |
| message_card.sender | body | string | 아니오 | max 50 | <!-- TODO: 용도 --> |
| message_card.recipient | body | string | 아니오 | max 50 | <!-- TODO: 용도 --> |
| message_card.content | body | string | 아니오 | max 200 | 본문 내용 |
| selected_options | body | array | 아니오 | max 20 | <!-- TODO: 용도 --> |
| tenant_id | body | integer | 아니오 | min 1 | tenant 식별자 |

**요청 예시**

```http
POST /api/plugins/sirsoft-flower_delivery/delivery-slots/reserve HTTP/1.1
Host: api.example.com
Accept: application/json
Authorization: Bearer {YOUR_TOKEN}
Content-Type: application/json

{
    "product_id": 1,
    "delivery_date": "2026-01-01",
    "time_slot": "예시값",
    "message_card": [
        "예시값"
    ],
    "message_card.sender": "예시값",
    "message_card.recipient": "예시값",
    "message_card.content": "예시 내용입니다.",
    "selected_options": [
        "예시값"
    ],
    "tenant_id": 1
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
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** <!-- TODO: 이 엔드포인트의 용도·주의사항·예시 시나리오를 작성하세요 -->


