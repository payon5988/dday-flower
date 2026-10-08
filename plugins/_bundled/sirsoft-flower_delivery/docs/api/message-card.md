# Message Card API 레퍼런스

> **소유**: plugin `sirsoft-flower_delivery` · **생성**: `php artisan api:docgen` (실측 기반). @generated 블록은 재생성 시 갱신되며, 사람이 작성한 설명은 보존됩니다.

---

## TL;DR (5초 요약)

```text
1. 이 문서는 실제 API 호출로 실측한 Message Card 엔드포인트 레퍼런스입니다
2. 각 엔드포인트: 메서드/URI/권한 + 요청 파라미터 표 + 요청 예시(raw HTTP) + 실측 응답 필드 표 + 응답 예시(envelope)
3. 응답 필드의 예시값·응답 예시 JSON 은 실제 호출 응답에서 관측된 값입니다
4. 갱신: 코드 변경 후 php artisan api:docgen 재실행
5. 설명(TODO) 칸은 사람이 채웁니다
```

---


### GET /api/plugins/sirsoft-flower_delivery/message-card/templates
<!-- @generated:start:api.plugins.sirsoft-flower_delivery.message-card.templates.index -->
- **라우트명**: `api.plugins.sirsoft-flower_delivery.message-card.templates.index`
- **컨트롤러**: `Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Public\ShowcaseController@cardTemplates`
- **인증/권한**: 공개 (인증 불필요)

**요청 파라미터**

_요청 파라미터 없음._

**요청 예시**

```http
GET /api/plugins/sirsoft-flower_delivery/message-card/templates HTTP/1.1
Host: api.example.com
Accept: application/json
```

**응답 필드** (`data` 내부)

_단건 응답: `data` 객체의 필드._

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| templates | array | `[{"key":"birthday","label":"생일 축하","content":"생일 축하해! 오늘처…` | 템플릿 목록 (각 원소 identifier/name 등 — 템플릿 관계 파생) |

**응답 예시**

<!-- @probed -->

```http
HTTP/1.1 200
```

```json
{
    "success": true,
    "message": "메시지 카드 템플릿을 조회했습니다.",
    "data": {
        "templates": [
            {
                "key": "birthday",
                "label": "생일 축하",
                "content": "생일 축하해! 오늘처럼 향기로운 날이 계속되길."
            },
            {
                "key": "thanks",
                "label": "감사",
                "content": "고마운 마음, 꽃에 담아 전합니다."
            },
            {
                "key": "love",
                "label": "사랑",
                "content": "당신과 함께하는 매일이 선물입니다."
            },
            {
                "key": "free",
                "label": "자유 작성",
                "content": ""
            }
        ]
    }
}
```

**에러 응답**

_대표 에러 없음 (공개 조회). <!-- TODO: 도메인 특이 에러가 있으면 보강 -->_

<!-- @generated:end -->

**설명** <!-- TODO: 이 엔드포인트의 용도·주의사항·예시 시나리오를 작성하세요 -->


