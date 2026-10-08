# 테스트 하니스 및 자동화 파이프라인

## 9.1 CI/CD 파이프라인 (GitHub Actions)
- `push` 또는 `pull_request` 시 다음 워크플로우 자동 실행:
  1. `composer install` 및 `npm install`
  2. `php artisan test` (PHPUnit)
  3. `npm run test` (Vitest - React 컴포넌트 테스트)
  4. Laravel Pint 코드 스타일 검사

## 9.2 E2E 테스트 하니스
- Playwright를 활용하여 '상품 선택 → 배송일 지정 → 카드 작성 → 토스페이먼츠 테스트 모드 결제 완료'까지의 전체 사용자 여정 자동화 스크립트 구축.