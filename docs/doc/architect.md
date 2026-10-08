# 시스템 아키텍처 명세서

## 2.1 기술 스택
- **Backend**: PHP 8.2+, Laravel 12.x, MySQL 8.0+, Redis 6.0+
- **Frontend**: React 19, Vite, Tailwind CSS 4 (다크 모드 및 커스텀 테마 지원)
- **인증**: Laravel Sanctum (Bearer Token)

## 2.2 아키텍처 패턴
- **Core-Extension 분리**: 그누보드7 코어는 건드리지 않고, `Modules`(이커머스) → `Plugins`(꽃배달 특화 로직) → `Templates`(감성 UI)의 3계층 구조 준수.
- **Hook System**: `Action`과 `Filter`를 사용하여 주문 완료 후 알림, 배송비 계산 로직 등을 비침투적으로 주입.

## 2.3 테넌시(Multi-tenancy) 전략 (향후 2단계)
- 단일 코드베이스에서 다중 테넌트 지원을 위해 `stancl/tenancy` 패키지 도입 검토.
- **방식**: Single-Database, Multi-Tenant (모든 커머스 테이블에 `tenant_id` 컬럼 추가 및 Global Scope 적용) 방식으로 초기 인프라 비용 최소화.
- **라우팅**: 서브도메인(`florist1.codeme.co.kr`) 또는 경로 기반 라우팅으로 테넌트 식별.

# 시스템 아키텍처 및 보안 경계 설계

## 2.1 계층형 보안 아키텍처
1. **Edge Layer**: Cloudflare WAF 적용 (SQLi, XSS, 악성 봇 차단). Rate Limiting 설정 (로그인 API 분당 5회).
2. **Application Layer (Laravel 12)**:
   - 모든 커스텀 꽃배달 로직은 `app/Modules/FlowerDelivery`에 격리.
   - **CSP (Content Security Policy)**: 헤더를 엄격하게 설정하여 허용된 도메인(예: Toss Payments, Kakao Map) 외의 스크립트 실행 차단.
3. **Data Layer**: 
   - 민감 정보(메시지 카드 내용, 상세 주소)는 애플리케이션 레벨에서 `AES-256-CBC` 암호화 후 DB 저장.

## 2.2 테넌시(SaaS) 준비 구조
- **Repository Pattern**: 모든 Eloquent 모델 호출은 `TenantScope`를 통해 자동으로 `where('tenant_id', current_tenant_id)`가 주입되도록 설계. 코어 수정 없이 서비스 확장 가능.