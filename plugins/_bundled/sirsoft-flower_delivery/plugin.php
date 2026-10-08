<?php

namespace Plugins\Sirsoft\FlowerDelivery;

use App\Enums\ExtensionOwnerType;
use App\Extension\AbstractPlugin;
use App\Extension\Helpers\ExtensionMenuSyncHelper;
use Plugins\Sirsoft\FlowerDelivery\Listeners\FlowerOrderAfterCreateListener;

/**
 * 꽃배달 확장 플러그인
 *
 * sirsoft-ecommerce 를 베이스로 프리미엄 꽃배달 특화 UI/UX와 배송 스케줄링을 제공합니다.
 * 코어·이커머스 수정 없이 훅으로만 격리 구현합니다.
 */
class Plugin extends AbstractPlugin
{
    /**
     * 관리자 메뉴 정의
     *
     * 코어 PluginManager 는 모듈과 달리 plugin 의 getAdminMenus() 를 자동 호출하지 않으므로
     * 본 클래스의 activate()/deactivate()/uninstall() 에서 직접 sync 한다.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAdminMenus(): array
    {
        return [
            [
                'name' => ['ko' => '꽃배달 슬롯 현황', 'en' => 'Flower Slot Board'],
                'slug' => 'sirsoft-flower_delivery-slot-board',
                'url' => '/admin/plugins/sirsoft-flower_delivery/slot-board',
                'icon' => 'fas fa-truck',
                'order' => 50,
            ],
            [
                'name' => ['ko' => '꽃배달 FAQ 관리', 'en' => 'Flower FAQ Management'],
                'slug' => 'sirsoft-flower_delivery-faq-board',
                'url' => '/admin/plugins/sirsoft-flower_delivery/faq-board',
                'icon' => 'fas fa-circle-question',
                'order' => 51,
            ],
        ];
    }

    /**
     * 플러그인 활성화 — 관리자 메뉴 자동 등록.
     *
     * @return bool 활성화 성공 여부
     */
    public function activate(): bool
    {
        $helper = app(ExtensionMenuSyncHelper::class);

        foreach ($this->getAdminMenus() as $menuData) {
            $helper->syncMenuRecursive(
                $menuData,
                ExtensionOwnerType::Plugin,
                $this->getIdentifier(),
            );
        }

        return true;
    }

    /**
     * 플러그인 비활성화 — 관리자 메뉴 일괄 제거.
     *
     * @return bool 비활성화 성공 여부
     */
    public function deactivate(): bool
    {
        app(ExtensionMenuSyncHelper::class)->cleanupStaleMenus(
            ExtensionOwnerType::Plugin,
            $this->getIdentifier(),
            currentSlugs: [],
        );

        return true;
    }

    /**
     * 플러그인 제거 — 메뉴 잔존 안전망.
     *
     * @return bool 제거 성공 여부
     */
    public function uninstall(): bool
    {
        $this->deactivate();

        return true;
    }

    /**
     * 플러그인이 제공하는 훅 정보 반환
     *
     * @return array 훅 정의 배열
     */
    public function getHooks(): array
    {
        return [
            [
                'name' => 'sirsoft-flower_delivery.slot.reserved',
                'type' => 'action',
                'description' => [
                    'ko' => '배송 슬롯 예약 완료 시 발화',
                    'en' => 'Fired when a delivery slot is reserved',
                ],
                'parameters' => [
                    'slot' => 'Model - FlowerDeliverySlot',
                ],
            ],
            [
                'name' => 'sirsoft-flower_delivery.share.message',
                'type' => 'filter',
                'description' => [
                    'ko' => 'SNS 공유 문구 생성 시 발화 (AI 플러그인이 덮어쓰는 확장점)',
                    'en' => 'Fired when building the SNS share message (AI plugins may override)',
                ],
                'parameters' => [
                    'payload' => 'array - 공유 페이로드 (message 키를 가공해 반환)',
                    'product' => 'Model - Product',
                ],
            ],
        ];
    }

    /**
     * 플러그인 권한 목록 반환 (계층 구조)
     *
     * @return array 권한 정의 배열
     */
    public function getPermissions(): array
    {
        return [
            'name' => [
                'ko' => '꽃배달 확장',
                'en' => 'Flower Delivery',
            ],
            'description' => [
                'ko' => '꽃배달 플러그인이 제공하는 권한',
                'en' => 'Permissions provided by the flower delivery plugin',
            ],
            'categories' => [
                [
                    'identifier' => 'slots',
                    'name' => ['ko' => '배송 슬롯', 'en' => 'Delivery Slots'],
                    'description' => [
                        'ko' => '배송 슬롯 도메인 권한 (조회·변경)',
                        'en' => 'Delivery slot domain permissions (view, update)',
                    ],
                    'permissions' => [
                        [
                            'action' => 'view',
                            'name' => ['ko' => '배송 슬롯 조회', 'en' => 'View Delivery Slots'],
                            'description' => [
                                'ko' => '배송 슬롯 조회',
                                'en' => 'View delivery slots',
                            ],
                            'type' => 'admin',
                            'roles' => ['admin'],
                        ],
                        [
                            'action' => 'update',
                            'name' => ['ko' => '배송 슬롯 변경', 'en' => 'Update Delivery Slots'],
                            'description' => [
                                'ko' => '배송 슬롯 활성/마감 변경',
                                'en' => 'Toggle delivery slot availability',
                            ],
                            'type' => 'admin',
                            'roles' => ['admin'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * 플러그인이 동적으로 생성한 테이블 목록 반환
     *
     * @return array 테이블명 배열
     */
    public function getDynamicTables(): array
    {
        return [
            'g7_flower_delivery_slots',
            'g7_flower_message_cards',
            'g7_flower_subscriptions',
            'g7_flower_options',
            'g7_flower_reservations',
            'g7_flower_faqs',
        ];
    }

    /**
     * 설치·업데이트 시 실행할 시더 목록 반환
     *
     * @return array 시더 클래스 배열
     */
    public function getSeeders(): array
    {
        return [];
    }

    /**
     * 플러그인 설정 값 반환 (기본값)
     *
     * 진입 파일은 src/ 클래스를 호출하지 않습니다 (신규 설치 Class not found 방지).
     *
     * @return array 설정 값 배열
     */
    public function getConfigValues(): array
    {
        return [
            'same_day_cutoff' => '14:00',
            'slot_cache_ttl' => 300,
        ];
    }

    /**
     * 플러그인 설정 스키마 반환
     *
     * @return array 설정 스키마
     */
    public function getSettingsSchema(): array
    {
        return [
            'same_day_cutoff' => [
                'type' => 'string',
                'default' => '14:00',
                'label' => ['ko' => '당일배송 마감 시각', 'en' => 'Same-day Cutoff'],
                'hint' => [
                    'ko' => 'HH:MM 형식. 이 시각 이후 당일 배송 슬롯은 마감 처리됩니다.',
                    'en' => 'HH:MM format. Same-day slots close after this time.',
                ],
                'required' => false,
            ],
            'slot_cache_ttl' => [
                'type' => 'integer',
                'default' => 300,
                'label' => ['ko' => '슬롯 조회 캐시 TTL(초)', 'en' => 'Slot Cache TTL (sec)'],
                'hint' => ['ko' => '배송 슬롯 조회 캐시 유지 시간', 'en' => 'Cache TTL for slot listing'],
                'required' => false,
            ],
        ];
    }

    /**
     * 플러그인 메타데이터 반환
     *
     * @return array 메타데이터
     */
    public function getMetadata(): array
    {
        return [
            'author' => 'Sirsoft',
            'license' => 'MIT',
            'homepage' => 'https://sir.kr',
            'keywords' => ['flower', 'delivery', 'ecommerce', 'subscription'],
        ];
    }

    /**
     * 훅 리스너 목록 반환
     *
     * @return array 훅 리스너 클래스 배열
     */
    public function getHookListeners(): array
    {
        return [
            FlowerOrderAfterCreateListener::class,
        ];
    }
}
