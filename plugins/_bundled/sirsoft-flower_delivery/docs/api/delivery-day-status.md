# Delivery Day Status API 레퍼런스

> **소유**: plugin `sirsoft-flower_delivery` · **생성**: `php artisan api:docgen` (실측 기반). @generated 블록은 재생성 시 갱신되며, 사람이 작성한 설명은 보존됩니다.

---

## TL;DR (5초 요약)

```text
1. 이 문서는 실제 API 호출로 실측한 Delivery Day Status 엔드포인트 레퍼런스입니다
2. 각 엔드포인트: 메서드/URI/권한 + 요청 파라미터 표 + 요청 예시(raw HTTP) + 실측 응답 필드 표 + 응답 예시(envelope)
3. 응답 필드의 예시값·응답 예시 JSON 은 실제 호출 응답에서 관측된 값입니다
4. 갱신: 코드 변경 후 php artisan api:docgen 재실행
5. 설명(TODO) 칸은 사람이 채웁니다
```

---


### GET /api/plugins/sirsoft-flower_delivery/delivery-day-status
<!-- @generated:start:api.plugins.sirsoft-flower_delivery.delivery-day-status.show -->
- **라우트명**: `api.plugins.sirsoft-flower_delivery.delivery-day-status.show`
- **컨트롤러**: `Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Public\ShowcaseController@dayStatus`
- **인증/권한**: 공개 (인증 불필요)

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| date | query | date | 아니오 | — | <!-- TODO: 용도 --> |
| product_id | query | integer | 아니오 | min 1 | product 식별자 |

**요청 예시**

```http
GET /api/plugins/sirsoft-flower_delivery/delivery-day-status?date=2026-01-01&product_id=1 HTTP/1.1
Host: api.example.com
Accept: application/json
```

**응답 필드** (`data` 내부)

_단건 응답: `data` 객체의 필드._

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| date | string | `2026-10-03` | <!-- TODO: 설명 --> |
| cutoff | string | `14:00` | <!-- TODO: 설명 --> |
| is_today | boolean | `true` | today 여부 |
| same_day_available | boolean | `false` | <!-- TODO: 설명 --> |
| same_day_remaining | integer | `0` | <!-- TODO: 설명 --> |

**응답 예시**

<!-- @probed -->

```http
HTTP/1.1 200
```

```json
{
    "success": true,
    "message": "당일배송 상태를 조회했습니다.",
    "data": {
        "date": "2026-10-03",
        "cutoff": "14:00",
        "is_today": true,
        "same_day_available": false,
        "same_day_remaining": 0
    }
}
```

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** <!-- TODO: 이 엔드포인트의 용도·주의사항·예시 시나리오를 작성하세요 -->


