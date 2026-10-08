# 프로그래밍 및 구현 가이드

## 5.1 코딩 표준
- PHP: Laravel Pint를 통한 PSR-12 준수.
- Frontend: React 19 함수형 컴포넌트, Tailwind CSS 4 유틸리티 클래스 우선 사용.

## 5.2 그누보드7 확장 개발 규칙
1. 새 기능은 반드시 `plugin/flower_delivery/` 디렉토리 내에 생성.
2. 코어 컨트롤러 수정 금지. 대신 `EventServiceProvider` 또는 G7의 Hook 시스템을 활용해 로직 주입.
3. 예: 주문 생성 후 메시지 카드 데이터 저장 → `g7_shop_order.after_create` 액션 훅에 리스너 등록.

## 5.3 AI 자동화 연계 (마스터님 선호 기능)
- 상품 등록 시 AI 프롬프트를 활용해 "감성 상품 설명" 자동 생성 기능 플러그인 포함.
- 주문 완료 시 SNS 공유용 이미지(4:5 비율)를 자동으로 생성하여 카카오톡으로 전송하는 기능 구현 [[0]].

# AI 코딩 에이전트 실행 프롬프트 (복사하여 사용)

"너는 Laravel 12와 React 19, Tailwind CSS 4 전문가야. 다음 요구사항에 맞춰 코드를 생성해줘.

1. **React 컴포넌트**: `FlowerDeliveryCalendar.tsx`
   - `react-day-picker`를 사용하여 달력 UI를 구현해.
   - 배송 가능 날짜만 `modifiers={{ available: true }}`로 지정하고, 딥 그린(#2D4A3E) 배경에 흰색 텍스트로 스타일링해.
   - 마감된 날짜는 회색 처리 및 클릭 비활성화.
   - 날짜 선택 시 하단에 해당 날짜의 시간대 슬롯(예: 10-12시, 14-16시)을 Framer Motion의 `AnimatePresence`를 이용해 부드럽게 슬라이드 업하여 표시해.

2. **Laravel Service**: `DeliverySlotService.php`
   - `bookSlot($productId, $date, $timeSlot)` 메서드를 작성해.
   - `DB::transaction`을 사용하고, `lockForUpdate()`를 걸어 동시성 이슈(이중 예약)를 원천 차단해.
   - `current_bookings`이 `max_capacity`을 초과하면 `CustomException('해당 시간대 배송이 마감되었습니다.')`를 던져.
"