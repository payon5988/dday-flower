prd.md를 읽고 무엇을 진행할 프로젝트인가를 염두해두고 진행해줘

순서 A: 데이터베이스 확장 (가장 먼저)
contraints.md를 먼저 읽고 아래 내용을 모두 검토해서 진행해줘
AI에게 database.md의 내용을 주고, Laravel Migration 파일을 생성하게 합니다.

기존 그누보드7 테이블을 건드리지 않고, g7_flower_delivery_slots 같은 새로운 테이블만 생성하도록 지시합니다.



순서 B: 백엔드 로직 격리 구현 (보안 및 기능)
AI에게 program.md와 api.md를 주고, app/Modules/FlowerDelivery 또는 plugins/flower_delivery 디렉토리를 생성하게 합니다.
핵심 지시사항: "그누보드7 코어 파일(app/Models/Order.php 등)을 절대 수정하지 말고, Event Listener나 Hook을 사용하여 배송 슬롯 검증 로직을 외부에서 주입(Inject)하라."
순서 C: UI/UX 프론트엔드 오버레이 (감성 구현)
AI에게 uiux.md와 program.md의 React 컴포넌트 프롬프트를 제공합니다.
기존 그누보드7의 기본 상점 페이지(shop/item.php 등)를 덮어쓰는 것이 아니라, 새로운 라우트(예: /flower-premium) 를 만들거나, 기존 뷰 파일의 특정 블록(Block)을 커스텀 React 컴포넌트(FlowerDeliveryCalendar.tsx)로 대체하도록 지시합니다.
Tailwind CSS 4의 tailwind.config.js에 마스터님이 선호하는 Deep Forest Green, Warm Cream 컬러를 먼저 정의하게 합니다.