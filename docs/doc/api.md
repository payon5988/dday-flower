# API 명세서 (RESTful, Laravel Sanctum 인증)

## 7.1 배송 슬롯 조회
- `GET /api/v1/shop/products/{id}/delivery-slots?date=2026-10-15`
- 응답: `{ "slots": [{"time": "10:00-12:00", "available": true, "remaining": 5}, ...] }`

## 7.2 주문 생성
- `POST /api/v1/shop/orders`
- 요청 바디: `{ "product_id": 1, "delivery_date": "2026-10-15", "delivery_slot": "14:00-16:00", "message_card": "행복한 생일!", "tenant_id": null }`

## 7.3 정기 구독 상태 변경
- `PATCH /api/v1/shop/subscriptions/{id}/pause` (배송 일시 정지)

# API 명세서 (보안 및 검증 강화)

## 6.1 배송 슬롯 예약 (동시성 제어 포함)
- **Endpoint**: `POST /api/v1/shop/delivery-slots/reserve`
- **Headers**: `Authorization: Bearer {token}`, `X-CSRF-TOKEN: {token}`
- **Request Body**:
  ```json
  {
    "product_id": 42,
    "delivery_date": "2026-10-15",
    "time_slot": "14:00-16:00",
    "message_card": {
      "sender": "김철수",
      "recipient": "이영희",
      "content": "생일 축하해!"
    }
  }

Validation Rules (Laravel FormRequest):
delivery_date: required|date|after_or_equal:today
message_card.content: required|string|max:200|regex:/^[a-zA-Z0-9가-힣\s\.\,\!\?]+$/ (특수문자 필터링)
Response (200 OK):

{
  "status": "success",
  "data": { "reservation_id": "RSV-20261015-001", "expires_in": 900 }
}